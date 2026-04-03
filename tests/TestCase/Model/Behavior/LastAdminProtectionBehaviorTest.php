<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Behavior;

use App\Model\Table\ProjectMembersTable;
use Cake\TestSuite\TestCase;

/**
 * LastAdminProtectionBehavior test case.
 */
class LastAdminProtectionBehaviorTest extends TestCase
{
    /**
     * @var \App\Model\Table\ProjectMembersTable
     */
    protected ProjectMembersTable $ProjectMembers;

    /**
     * @var array<string>
     */
    protected array $fixtures = [
        'app.ProjectMembers',
        'app.Projects',
        'app.Users',
        'app.ActivityLog',
    ];

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->ProjectMembers = $this->getTableLocator()->get('ProjectMembers');
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->ProjectMembers);

        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testBehaviorAttachedAndPreventsLastAdminChanges(): void
    {
        $this->assertTrue($this->ProjectMembers->behaviors()->has('LastAdminProtection'));

        $member = $this->ProjectMembers->get(1);
        $member = $this->ProjectMembers->patchEntity($member, ['role' => 'member']);
        $this->assertFalse($this->ProjectMembers->save($member));
        $this->assertArrayHasKey('role', $member->getErrors());

        $member = $this->ProjectMembers->get(1);
        $this->assertFalse($this->ProjectMembers->delete($member));
        $this->assertArrayHasKey('role', $member->getErrors());
    }
}
