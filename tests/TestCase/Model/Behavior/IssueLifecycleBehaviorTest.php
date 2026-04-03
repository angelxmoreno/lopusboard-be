<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Behavior;

use App\Model\Enum\IssueType;
use App\Model\Table\IssuesTable;
use Cake\TestSuite\TestCase;

/**
 * IssueLifecycleBehavior test case.
 */
class IssueLifecycleBehaviorTest extends TestCase
{
    /**
     * @var \App\Model\Table\IssuesTable
     */
    protected IssuesTable $Issues;

    /**
     * @var array<string>
     */
    protected array $fixtures = [
        'app.ActivityLog',
        'app.Issues',
        'app.Projects',
        'app.Statuses',
        'app.Users',
        'app.Departments',
    ];

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->Issues = $this->getTableLocator()->get('Issues');
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Issues);

        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testBehaviorAttachedAndAssignsProjectAndPosition(): void
    {
        $this->assertTrue($this->Issues->behaviors()->has('IssueLifecycle'));

        $issue = $this->Issues->newEntity([
            'project_id' => 2,
            'parent_id' => 1,
            'type' => 'task',
            'title' => 'Derived Task',
            'status_id' => 1,
            'priority' => 'medium',
            'created_by' => 1,
        ], ['accessibleFields' => ['project_id' => true, 'created_by' => true]]);

        $saved = $this->Issues->save($issue);

        $this->assertNotFalse($saved);
        $this->assertSame(1, (int)$issue->project_id);
        $this->assertSame(IssueType::Task->value, $issue->type);
        $this->assertSame(4.5, (float)$issue->position);
    }

    /**
     * @return void
     */
    public function testBehaviorRejectsProjectMutationAfterCreate(): void
    {
        $issue = $this->Issues->get(1);
        $issue->set('project_id', 2, ['guard' => false]);

        $this->assertFalse($this->Issues->save($issue));
        $this->assertSame(1, (int)$this->Issues->get(1)->project_id);
    }
}
