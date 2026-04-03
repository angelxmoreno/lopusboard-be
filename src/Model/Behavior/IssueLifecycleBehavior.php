<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use App\Model\Enum\IssueType;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;

/**
 * Manages issue save lifecycle rules.
 */
class IssueLifecycleBehavior extends Behavior
{
    /**
     * Applies derived issue fields before validation runs.
     *
     * @param \Cake\Event\EventInterface $event The ORM event instance.
     * @param \ArrayObject<string, mixed> $data The marshalled request data.
     * @param \ArrayObject<string, mixed> $options Marshal options.
     * @return void
     */
    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options): void
    {
        $parentId = $data['parent_id'] ?? null;
        if ($parentId !== null) {
            $parentIssue = $this->table()->getAssociation('ParentIssues')->getTarget()
                ->find()
                ->select(['project_id'])
                ->where(['ParentIssues.id' => $parentId])
                ->disableHydration()
                ->first();

            if ($parentIssue !== null) {
                $data['project_id'] = (int)$parentIssue['project_id'];
                if (!isset($data['type']) || $data['type'] === '') {
                    $data['type'] = IssueType::Task->value;
                }
            }
        }

        if (
            (!isset($data['position']) || $data['position'] === '' || $data['position'] === null) &&
            isset($data['project_id'])
        ) {
            $data['position'] = $this->nextPositionForProject((int)$data['project_id']);
        }
    }

    /**
     * Applies issue lifecycle rules before persistence.
     *
     * @param \Cake\Event\EventInterface $event The ORM event instance.
     * @param \Cake\Datasource\EntityInterface $entity The issue entity being saved.
     * @param \ArrayObject<string, mixed> $options Save options.
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        if (!$entity->isNew() && $entity->isDirty('project_id')) {
            $entity->setError('project_id', __('Project cannot be changed after creation.'));
            $event->stopPropagation();
            $event->setResult(false);

            return;
        }

        if ($entity->isNew() && (!$entity->isDirty('position') || $entity->get('position') === null)) {
            $projectId = $entity->get('project_id');
            if ($projectId !== null) {
                $nextPosition = $this->nextPositionForProject((int)$projectId);
                $entity->set('position', $nextPosition);
            }
        }
    }

    /**
     * Calculates the next position for issues within a project.
     *
     * @param int $projectId The project identifier.
     * @return float
     */
    public function nextPositionForProject(int $projectId): float
    {
        $maxPosition = $this->table()->find()
            ->select(['max_position' => $this->table()->find()->func()->max('position')])
            ->where(['Issues.project_id' => $projectId])
            ->disableHydration()
            ->first();

        $currentMax = $maxPosition['max_position'] ?? 0;

        return (float)$currentMax + 1.0;
    }
}
