<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Table\StatusesTable;
use App\Policy\StatusesTablePolicy;
use App\Test\Support\Auth\TestAuthorizationIdentity;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;

class StatusesTablePolicyTest extends TestCase
{
    use LocatorAwareTrait;

    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Projects',
        'app.ProjectMembers',
        'app.Statuses',
    ];

    public function testCanIndexAndCanAddRequireAuthentication(): void
    {
        $policy = new StatusesTablePolicy();

        $this->assertFalse($policy->canIndex(new TestAuthorizationIdentity([])));
        $this->assertFalse($policy->canAdd(new TestAuthorizationIdentity([])));
        $this->assertTrue($policy->canIndex(new TestAuthorizationIdentity(['id' => 1])));
        $this->assertTrue($policy->canAdd(new TestAuthorizationIdentity(['id' => 1])));
    }

    public function testScopeIndexRestrictsResultsToMemberProjects(): void
    {
        $policy = new StatusesTablePolicy();

        $results = $policy
            ->scopeIndex(new TestAuthorizationIdentity(['id' => 2]), $this->statuses()->find())
            ->all()
            ->extract('id')
            ->toList();

        $this->assertSame([1, 3], $results);
    }

    private function statuses(): StatusesTable
    {
        /** @var \App\Model\Table\StatusesTable */
        return $this->getTableLocator()->get(StatusesTable::class);
    }
}
