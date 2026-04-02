<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * IssuesFixture
 */
class IssuesFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'project_id' => 1,
                'parent_id' => null,
                'type' => 'issue',
                'title' => 'Issue One',
                'description' => 'First issue fixture.',
                'status_id' => 1,
                'priority' => 'medium',
                'assignee_id' => 1,
                'department_id' => 1,
                'due_date' => '2026-03-31',
                'position' => 1.5,
                'created_by' => 1,
                'created' => '2026-03-31 05:06:15',
                'modified' => '2026-03-31 05:06:15',
            ],
            [
                'id' => 2,
                'project_id' => 2,
                'parent_id' => null,
                'type' => 'issue',
                'title' => 'Issue Two',
                'description' => 'Second issue fixture.',
                'status_id' => 2,
                'priority' => 'high',
                'assignee_id' => 2,
                'department_id' => 2,
                'due_date' => '2026-04-01',
                'position' => 2.5,
                'created_by' => 2,
                'created' => '2026-03-31 05:06:15',
                'modified' => '2026-03-31 05:06:15',
            ],
            [
                'id' => 3,
                'project_id' => 1,
                'parent_id' => 1,
                'type' => 'task',
                'title' => 'Task One',
                'description' => 'Task fixture under Issue One.',
                'status_id' => 1,
                'priority' => 'low',
                'assignee_id' => 1,
                'department_id' => 1,
                'due_date' => '2026-04-02',
                'position' => 3.5,
                'created_by' => 1,
                'created' => '2026-03-31 05:06:15',
                'modified' => '2026-03-31 05:06:15',
            ],
        ];
        parent::init();
    }
}
