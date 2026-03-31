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
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Issues') ? [] : ['className' => IssuesTable::class];
        $this->Issues = $this->getTableLocator()->get('Issues', $config);
        $this->Issues->setSchema([
            'id' => ['type' => 'integer'],
            'project_id' => ['type' => 'integer'],
            'type' => ['type' => 'string'],
            'status_id' => ['type' => 'integer'],
            'position' => ['type' => 'decimal'],
            'assignee_id' => ['type' => 'integer', 'null' => true],
            'department_id' => ['type' => 'integer', 'null' => true],
        ]);
        $this->Issues->getAssociation('Statuses')->getTarget()->setSchema([
            'id' => ['type' => 'integer'],
            'name' => ['type' => 'string'],
        ]);
        $this->Issues->getAssociation('Assignees')->getTarget()->setSchema([
            'id' => ['type' => 'integer'],
            'name' => ['type' => 'string'],
            'email' => ['type' => 'string'],
        ]);
        $this->Issues->getAssociation('Departments')->getTarget()->setSchema([
            'id' => ['type' => 'integer'],
            'name' => ['type' => 'string'],
        ]);
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
