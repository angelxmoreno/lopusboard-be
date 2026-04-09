<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Table\ProjectMembersTable;
use App\Policy\ProjectMembersTablePolicy;
use App\Test\Support\Auth\TestAuthorizationIdentity;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;

class ProjectMembersTablePolicyTest extends TestCase
{
    use LocatorAwareTrait;

    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Projects',
        'app.ProjectMembers',
    ];

    public function testCanIndexAndCanAddRequireAuthentication(): void
    {
        $policy = new ProjectMembersTablePolicy();

        $this->assertFalse($policy->canIndex(new TestAuthorizationIdentity([])));
        $this->assertFalse($policy->canAdd(new TestAuthorizationIdentity([])));
        $this->assertTrue($policy->canIndex(new TestAuthorizationIdentity(['id' => 1])));
        $this->assertTrue($policy->canAdd(new TestAuthorizationIdentity(['id' => 1])));
    }

    public function testScopeIndexRestrictsResultsToAdminProjects(): void
    {
        $policy = new ProjectMembersTablePolicy();
        $query = $this->projectMembers()->find();

        $adminResults = $policy
            ->scopeIndex(new TestAuthorizationIdentity(['id' => 1]), $query)
            ->all()
            ->extract('id')
            ->toList();

        $memberResults = $policy
            ->scopeIndex(new TestAuthorizationIdentity(['id' => 2]), $this->projectMembers()->find())
            ->all()
            ->extract('id')
            ->toList();

        $this->assertSame([1, 2], $adminResults);
        $this->assertSame([], $memberResults);
    }

    private function projectMembers(): ProjectMembersTable
    {
        /** @var \App\Model\Table\ProjectMembersTable */
        return $this->getTableLocator()->get(ProjectMembersTable::class);
    }
}
