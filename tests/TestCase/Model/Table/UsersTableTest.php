<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UsersTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\UsersTable Test Case
 */
class UsersTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\UsersTable
     */
    protected $Users;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Users') ? [] : ['className' => UsersTable::class];
        $this->Users = $this->getTableLocator()->get('Users', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Users);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\UsersTable::validationDefault()
     */
    public function testInitializeAssociations(): void
    {
        $this->assertSame('created_by', $this->Users->getAssociation('Projects')->getForeignKey());
        $this->assertSame('created_by', $this->Users->getAssociation('Issues')->getForeignKey());
        $this->assertSame('Issues', $this->Users->getAssociation('AssignedIssues')->getClassName());
        $this->assertSame('assignee_id', $this->Users->getAssociation('AssignedIssues')->getForeignKey());
        $this->assertSame('created_by', $this->Users->getAssociation('WikiPages')->getForeignKey());
        $this->assertSame('actor_id', $this->Users->getAssociation('ActivityLog')->getForeignKey());
    }
}
