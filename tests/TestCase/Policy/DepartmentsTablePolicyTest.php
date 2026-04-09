<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Table\DepartmentsTable;
use App\Policy\DepartmentsTablePolicy;
use App\Test\Support\Auth\TestAuthorizationIdentity;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;

class DepartmentsTablePolicyTest extends TestCase
{
    use LocatorAwareTrait;

    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Projects',
        'app.ProjectMembers',
        'app.Departments',
    ];

    public function testCanIndexAndCanAddRequireAuthentication(): void
    {
        $policy = new DepartmentsTablePolicy();

        $this->assertFalse($policy->canIndex(new TestAuthorizationIdentity([])));
        $this->assertFalse($policy->canAdd(new TestAuthorizationIdentity([])));
        $this->assertTrue($policy->canIndex(new TestAuthorizationIdentity(['id' => 1])));
        $this->assertTrue($policy->canAdd(new TestAuthorizationIdentity(['id' => 1])));
    }

    public function testScopeIndexRestrictsResultsToMemberProjects(): void
    {
        $policy = new DepartmentsTablePolicy();

        $results = $policy
            ->scopeIndex(new TestAuthorizationIdentity(['id' => 2]), $this->departments()->find())
            ->all()
            ->extract('id')
            ->toList();

        $this->assertSame([1], $results);
    }

    private function departments(): DepartmentsTable
    {
        /** @var \App\Model\Table\DepartmentsTable */
        return $this->getTableLocator()->get(DepartmentsTable::class);
    }
}
