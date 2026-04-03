<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use App\Model\Enum\ProjectMemberRole;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;
use RuntimeException;

/**
 * Seeds default project records after a project is created.
 */
class ProjectSeedingBehavior extends Behavior
{
    /**
     * Seeds the default statuses, departments, and admin membership.
     *
     * @param \Cake\Event\EventInterface $event The ORM event instance.
     * @param \Cake\Datasource\EntityInterface $entity The saved project entity.
     * @param \ArrayObject<string, mixed> $options Save options.
     * @return void
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        if (!$entity->isNew()) {
            return;
        }

        $projectId = (int)$entity->get('id');
        $creatorId = (int)$entity->get('created_by');

        $statusesTable = $this->table()->getAssociation('Statuses')->getTarget();
        $departmentsTable = $this->table()->getAssociation('Departments')->getTarget();
        $projectMembersTable = $this->table()->getAssociation('ProjectMembers')->getTarget();

        $statuses = $statusesTable->newEntities($this->defaultStatuses($projectId), [
            'accessibleFields' => [
                'project_id' => true,
                'name' => true,
                'color' => true,
                'position' => true,
                'is_default' => true,
                'is_done' => true,
            ],
        ]);
        if ($statusesTable->saveMany($statuses) === false) {
            throw new RuntimeException('Unable to seed project statuses.');
        }

        $departments = $departmentsTable->newEntities($this->defaultDepartments($projectId), [
            'accessibleFields' => [
                'project_id' => true,
                'name' => true,
                'color' => true,
                'position' => true,
            ],
        ]);
        if ($departmentsTable->saveMany($departments) === false) {
            throw new RuntimeException('Unable to seed project departments.');
        }

        $adminMembership = $projectMembersTable->newEntity(
            [
                'project_id' => $projectId,
                'user_id' => $creatorId,
                'role' => ProjectMemberRole::Admin->value,
            ],
            ['accessibleFields' => ['project_id' => true, 'user_id' => true, 'role' => true]],
        );
        if ($projectMembersTable->save($adminMembership) === false) {
            throw new RuntimeException('Unable to seed project admin membership.');
        }
    }

    /**
     * Returns the default status seed set for a new project.
     *
     * @param int $projectId The project identifier.
     * @return array<int, array<string, mixed>>
     */
    public function defaultStatuses(int $projectId): array
    {
        return [
            [
                'project_id' => $projectId,
                'name' => 'Backlog',
                'color' => '#6B7280',
                'position' => 1.0,
                'is_default' => 0,
                'is_done' => 0,
            ],
            [
                'project_id' => $projectId,
                'name' => 'Todo',
                'color' => '#3B82F6',
                'position' => 2.0,
                'is_default' => 1,
                'is_done' => 0,
            ],
            [
                'project_id' => $projectId,
                'name' => 'In Progress',
                'color' => '#F59E0B',
                'position' => 3.0,
                'is_default' => 0,
                'is_done' => 0,
            ],
            [
                'project_id' => $projectId,
                'name' => 'In Review',
                'color' => '#8B5CF6',
                'position' => 4.0,
                'is_default' => 0,
                'is_done' => 0,
            ],
            [
                'project_id' => $projectId,
                'name' => 'Done',
                'color' => '#10B981',
                'position' => 5.0,
                'is_default' => 0,
                'is_done' => 1,
            ],
        ];
    }

    /**
     * Returns the default department seed set for a new project.
     *
     * @param int $projectId The project identifier.
     * @return array<int, array<string, mixed>>
     */
    public function defaultDepartments(int $projectId): array
    {
        return [
            [
                'project_id' => $projectId,
                'name' => 'Engineering',
                'color' => '#2563EB',
                'position' => 1.0,
            ],
            [
                'project_id' => $projectId,
                'name' => 'Design',
                'color' => '#DB2777',
                'position' => 2.0,
            ],
            [
                'project_id' => $projectId,
                'name' => 'Product',
                'color' => '#7C3AED',
                'position' => 3.0,
            ],
            [
                'project_id' => $projectId,
                'name' => 'Marketing',
                'color' => '#EA580C',
                'position' => 4.0,
            ],
        ];
    }
}
