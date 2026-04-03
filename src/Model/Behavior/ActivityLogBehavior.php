<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;

/**
 * Writes normalized activity log rows for model mutations.
 */
class ActivityLogBehavior extends Behavior
{
    /**
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'subjectType' => null,
        'actorField' => null,
        'actorResolver' => null,
        'projectField' => null,
        'projectResolver' => null,
        'createAction' => null,
        'updateAction' => null,
        'deleteAction' => null,
        'fieldActions' => [],
        'ignoredFields' => ['created', 'modified'],
    ];

    /**
     * Captures field changes before update saves.
     *
     * @param \Cake\Event\EventInterface $event The beforeSave event.
     * @param \Cake\Datasource\EntityInterface $entity The entity being saved.
     * @param \ArrayObject<string, mixed> $options Save options.
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        if ($entity->isNew()) {
            return;
        }

        $dirtyFields = array_values(array_diff($entity->getDirty(), $this->getConfig('ignoredFields')));
        if ($dirtyFields === []) {
            return;
        }

        $oldValues = [];
        $newValues = [];
        foreach ($dirtyFields as $field) {
            $oldValues[$field] = $entity->getOriginal($field);
            $newValues[$field] = $entity->get($field);
        }

        $options['_activity_old_values'] = $oldValues;
        $options['_activity_new_values'] = $newValues;
    }

    /**
     * Writes create and update activity rows after save.
     *
     * @param \Cake\Event\EventInterface $event The afterSave event.
     * @param \Cake\Datasource\EntityInterface $entity The saved entity.
     * @param \ArrayObject<string, mixed> $options Save options.
     * @return void
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        $projectId = $this->resolveProjectId($entity, $options);
        $actorId = $this->resolveActorId($entity, $options);
        $subjectType = $this->resolveSubjectType($entity, $options);
        $subjectId = (int)$entity->get('id');

        if ($projectId === null || $actorId === null || $subjectType === null || $subjectId === 0) {
            return;
        }

        if ($entity->isNew()) {
            $this->writeLog(
                $projectId,
                $actorId,
                $subjectType,
                $subjectId,
                (string)$this->getConfig('createAction'),
                null,
                $this->entitySnapshot($entity),
            );

            return;
        }

        $oldValues = $options['_activity_old_values'] ?? [];
        $newValues = $options['_activity_new_values'] ?? [];
        if ($oldValues === [] || $newValues === []) {
            return;
        }

        $remainingOld = $oldValues;
        $remainingNew = $newValues;
        foreach ($this->getConfig('fieldActions') as $field => $actionResolver) {
            if (!array_key_exists($field, $oldValues) || !array_key_exists($field, $newValues)) {
                continue;
            }

            $action = $this->resolveFieldAction(
                $actionResolver,
                $oldValues[$field],
                $newValues[$field],
                $entity,
                $options,
            );
            if ($action === null) {
                continue;
            }

            $this->writeLog(
                $projectId,
                $actorId,
                $subjectType,
                $subjectId,
                $action,
                [$field => $oldValues[$field]],
                [$field => $newValues[$field]],
            );
            unset($remainingOld[$field], $remainingNew[$field]);
        }

        if ($remainingOld !== [] || $remainingNew !== []) {
            $this->writeLog(
                $projectId,
                $actorId,
                $subjectType,
                $subjectId,
                (string)$this->getConfig('updateAction'),
                $remainingOld === [] ? null : $remainingOld,
                $remainingNew === [] ? null : $remainingNew,
            );
        }
    }

    /**
     * Captures entity state before deletion.
     *
     * @param \Cake\Event\EventInterface $event The beforeDelete event.
     * @param \Cake\Datasource\EntityInterface $entity The entity being deleted.
     * @param \ArrayObject<string, mixed> $options Delete options.
     * @return void
     */
    public function beforeDelete(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        $options['_activity_deleted_snapshot'] = $this->entitySnapshot($entity);
    }

    /**
     * Writes delete activity rows after deletion.
     *
     * @param \Cake\Event\EventInterface $event The afterDelete event.
     * @param \Cake\Datasource\EntityInterface $entity The deleted entity.
     * @param \ArrayObject<string, mixed> $options Delete options.
     * @return void
     */
    public function afterDelete(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        $projectId = $this->resolveProjectId($entity, $options);
        $actorId = $this->resolveActorId($entity, $options);
        $subjectType = $this->resolveSubjectType($entity, $options);
        $subjectId = (int)$entity->get('id');

        if ($projectId === null || $actorId === null || $subjectType === null || $subjectId === 0) {
            return;
        }

        $this->writeLog(
            $projectId,
            $actorId,
            $subjectType,
            $subjectId,
            (string)$this->getConfig('deleteAction'),
            $options['_activity_deleted_snapshot'] ?? null,
            null,
        );
    }

    /**
     * Creates a normalized snapshot of persisted columns.
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity to snapshot.
     * @return array<string, mixed>
     */
    protected function entitySnapshot(EntityInterface $entity): array
    {
        /** @var list<string> $columns */
        $columns = $this->table()->getSchema()->columns();

        return $entity->extract($columns);
    }

    /**
     * Resolves the action name for a field-specific change.
     *
     * @param mixed $resolver The configured resolver.
     * @param mixed $oldValue The previous field value.
     * @param mixed $newValue The new field value.
     * @param \Cake\Datasource\EntityInterface $entity The current entity.
     * @param \ArrayObject<string, mixed> $options Save options.
     * @return string|null
     */
    protected function resolveFieldAction(
        mixed $resolver,
        mixed $oldValue,
        mixed $newValue,
        EntityInterface $entity,
        ArrayObject $options,
    ): ?string {
        if (is_string($resolver)) {
            return $resolver;
        }
        if (is_callable($resolver)) {
            return $resolver($oldValue, $newValue, $entity, $options);
        }

        return null;
    }

    /**
     * Resolves the activity subject type.
     *
     * @param \Cake\Datasource\EntityInterface $entity The mutated entity.
     * @param \ArrayObject<string, mixed> $options Persistence options.
     * @return string|null
     */
    protected function resolveSubjectType(EntityInterface $entity, ArrayObject $options): ?string
    {
        $subjectType = $this->getConfig('subjectType');
        if (is_string($subjectType)) {
            return $subjectType;
        }
        if (is_callable($subjectType)) {
            return $subjectType($entity, $options);
        }

        return null;
    }

    /**
     * Resolves the actor identifier for an activity row.
     *
     * @param \Cake\Datasource\EntityInterface $entity The mutated entity.
     * @param \ArrayObject<string, mixed> $options Persistence options.
     * @return int|null
     */
    protected function resolveActorId(EntityInterface $entity, ArrayObject $options): ?int
    {
        if (isset($options['actor_id'])) {
            return (int)$options['actor_id'];
        }

        $resolver = $this->getConfig('actorResolver');
        if (is_callable($resolver)) {
            $actorId = $resolver($entity, $options);

            return $actorId === null ? null : (int)$actorId;
        }

        $field = $this->getConfig('actorField');
        if (is_string($field) && $entity->get($field) !== null) {
            return (int)$entity->get($field);
        }

        return null;
    }

    /**
     * Resolves the project identifier for an activity row.
     *
     * @param \Cake\Datasource\EntityInterface $entity The mutated entity.
     * @param \ArrayObject<string, mixed> $options Persistence options.
     * @return int|null
     */
    protected function resolveProjectId(EntityInterface $entity, ArrayObject $options): ?int
    {
        if (isset($options['project_id'])) {
            return (int)$options['project_id'];
        }

        $resolver = $this->getConfig('projectResolver');
        if (is_callable($resolver)) {
            $projectId = $resolver($entity, $options);

            return $projectId === null ? null : (int)$projectId;
        }

        $field = $this->getConfig('projectField');
        if (is_string($field) && $entity->get($field) !== null) {
            return (int)$entity->get($field);
        }

        return null;
    }

    /**
     * Persists a single activity row.
     *
     * @param int $projectId The project identifier.
     * @param int $actorId The actor identifier.
     * @param string $subjectType The subject type.
     * @param int $subjectId The subject identifier.
     * @param string $action The action name.
     * @param array<string, mixed>|null $oldValue The previous values.
     * @param array<string, mixed>|null $newValue The new values.
     * @return void
     */
    protected function writeLog(
        int $projectId,
        int $actorId,
        string $subjectType,
        int $subjectId,
        string $action,
        ?array $oldValue,
        ?array $newValue,
    ): void {
        $activityLogTable = $this->table()->getTableLocator()->get('ActivityLog');
        $log = $activityLogTable->newEntity(
            [
                'project_id' => $projectId,
                'actor_id' => $actorId,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'action' => $action,
                'old_value' => $oldValue === null ? null : json_encode($oldValue),
                'new_value' => $newValue === null ? null : json_encode($newValue),
            ],
            [
                'accessibleFields' => [
                    'project_id' => true,
                    'actor_id' => true,
                    'subject_type' => true,
                    'subject_id' => true,
                    'action' => true,
                    'old_value' => true,
                    'new_value' => true,
                ],
            ],
        );

        $activityLogTable->saveOrFail($log);
    }
}
