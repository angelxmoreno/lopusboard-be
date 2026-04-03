<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use App\Model\Enum\ProjectMemberRole;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;

/**
 * Prevents projects from losing their final admin member.
 */
class LastAdminProtectionBehavior extends Behavior
{
    /**
     * Prevents demoting the final admin of a project.
     *
     * @param \Cake\Event\EventInterface $event The beforeSave event.
     * @param \Cake\Datasource\EntityInterface $entity The member being saved.
     * @param \ArrayObject<string, mixed> $options Save options.
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        if (
            !$entity->isNew() &&
            $entity->isDirty('role') &&
            $entity->getOriginal('role') === ProjectMemberRole::Admin->value &&
            $entity->get('role') !== ProjectMemberRole::Admin->value &&
            $this->isLastAdmin((int)$entity->get('project_id'), (int)$entity->get('id'))
        ) {
            $entity->setError('role', __('A project must have at least one admin.'));
            $event->stopPropagation();
            $event->setResult(false);
        }
    }

    /**
     * Prevents deleting the final admin of a project.
     *
     * @param \Cake\Event\EventInterface $event The beforeDelete event.
     * @param \Cake\Datasource\EntityInterface $entity The member being deleted.
     * @param \ArrayObject<string, mixed> $options Delete options.
     * @return void
     */
    public function beforeDelete(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        if (
            $entity->get('role') === ProjectMemberRole::Admin->value &&
            $this->isLastAdmin((int)$entity->get('project_id'), (int)$entity->get('id'))
        ) {
            $entity->setError('role', __('A project must have at least one admin.'));
            $event->stopPropagation();
            $event->setResult(false);
        }
    }

    /**
     * Checks whether the given member is the final admin for a project.
     *
     * @param int $projectId The project identifier.
     * @param int $memberId The member identifier to exclude from the count.
     * @return bool
     */
    public function isLastAdmin(int $projectId, int $memberId): bool
    {
        $adminCount = $this->table()->find()
            ->where([
                'project_id' => $projectId,
                'role' => ProjectMemberRole::Admin->value,
                'id !=' => $memberId,
            ])
            ->count();

        return $adminCount === 0;
    }
}
