<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ProjectMembersTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ProjectMembersTable Test Case
 */
class ProjectMembersTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ProjectMembersTable
     */
    protected $ProjectMembers;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.ProjectMembers',
        'app.Projects',
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('ProjectMembers') ? [] : ['className' => ProjectMembersTable::class];
        $this->ProjectMembers = $this->getTableLocator()->get('ProjectMembers', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->ProjectMembers);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ProjectMembersTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $member = $this->ProjectMembers->newEntity(
            [
                'project_id' => 1,
                'user_id' => 1,
                'role' => 'viewer',
            ],
            ['accessibleFields' => ['*' => true]],
        );

        $this->assertArrayHasKey('role', $member->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ProjectMembersTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $member = $this->ProjectMembers->newEntity(
            [
                'project_id' => 1,
                'user_id' => 1,
                'role' => 'admin',
            ],
            ['accessibleFields' => ['*' => true]],
        );

        $this->assertFalse($this->ProjectMembers->save($member));
        $this->assertArrayHasKey('project_id', $member->getErrors());
    }
}
