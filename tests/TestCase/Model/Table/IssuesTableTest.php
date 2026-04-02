<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\IssuesTable;
use Cake\Database\ValueBinder;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\IssuesTable Test Case
 */
class IssuesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\IssuesTable
     */
    protected $Issues;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Issues',
        'app.Projects',
        'app.Statuses',
        'app.Users',
        'app.Departments',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Issues') ? [] : ['className' => IssuesTable::class];
        $this->Issues = $this->getTableLocator()->get('Issues', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Issues);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\IssuesTable::validationDefault()
     */
    public function testInitializeAssociations(): void
    {
        $this->assertSame('Users', $this->Issues->getAssociation('Creators')->getClassName());
        $this->assertSame('created_by', $this->Issues->getAssociation('Creators')->getForeignKey());
        $this->assertSame('Issues', $this->Issues->getAssociation('Tasks')->getClassName());
        $this->assertSame('parent_id', $this->Issues->getAssociation('Tasks')->getForeignKey());
        $this->assertSame('subject_id', $this->Issues->getAssociation('ActivityLog')->getForeignKey());
    }

    /**
     * @return void
     */
    public function testValidationDefaultRejectsInvalidTypeAndPriority(): void
    {
        $issue = $this->Issues->newEntity(
            [
                'project_id' => 1,
                'type' => 'epic',
                'title' => 'Bad Issue',
                'status_id' => 1,
                'priority' => 'urgent',
                'position' => 1.0,
                'created_by' => 1,
            ],
            ['accessibleFields' => ['*' => true]],
        );

        $this->assertArrayHasKey('type', $issue->getErrors());
        $this->assertArrayHasKey('priority', $issue->getErrors());
    }

    /**
     * @return void
     */
    public function testBuildRulesRejectStatusFromAnotherProject(): void
    {
        $issue = $this->Issues->newEntity(
            [
                'project_id' => 1,
                'type' => 'issue',
                'title' => 'Cross Project Status',
                'status_id' => 2,
                'priority' => 'medium',
                'position' => 3.0,
                'created_by' => 1,
            ],
            ['accessibleFields' => ['*' => true]],
        );

        $this->assertFalse($this->Issues->save($issue));
        $this->assertArrayHasKey('status_id', $issue->getErrors());
    }

    /**
     * @return void
     */
    public function testBuildRulesRejectParentFromAnotherProject(): void
    {
        $issue = $this->Issues->newEntity(
            [
                'project_id' => 1,
                'parent_id' => 2,
                'type' => 'task',
                'title' => 'Cross Project Parent',
                'status_id' => 1,
                'priority' => 'medium',
                'position' => 4.0,
                'created_by' => 1,
            ],
            ['accessibleFields' => ['*' => true]],
        );

        $this->assertFalse($this->Issues->save($issue));
        $this->assertArrayHasKey('parent_id', $issue->getErrors());
    }

    /**
     * Test findForKanban method
     *
     * @return void
     * @link \App\Model\Table\IssuesTable::findForKanban()
     */
    public function testFindForKanban(): void
    {
        $query = $this->Issues->findForKanban($this->Issues->find(), ['project_id' => 1]);
        $where = $query->clause('where')->sql(new ValueBinder());
        $order = $query->clause('order')->sql(new ValueBinder());
        $contain = $query->getContain();

        $this->assertStringContainsString('Issues.project_id', $where);
        $this->assertStringContainsString('Issues.type', $where);
        $this->assertStringContainsString('Issues.status_id', $order);
        $this->assertStringContainsString('Issues.position', $order);
        $this->assertArrayHasKey('Statuses', $contain);
        $this->assertArrayHasKey('Assignees', $contain);
        $this->assertArrayHasKey('Departments', $contain);
    }
}
