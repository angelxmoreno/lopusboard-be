<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Table\IssuesTable;
use App\Policy\IssuesTablePolicy;
use App\Test\Support\Auth\TestAuthorizationIdentity;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;

class IssuesTablePolicyTest extends TestCase
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
        'app.Departments',
        'app.Issues',
    ];

    public function testScopeIndexRestrictsResultsToAccessibleProjects(): void
    {
        $policy = new IssuesTablePolicy();

        $results = $policy
            ->scopeIndex(new TestAuthorizationIdentity(['id' => 2]), $this->issues()->find())
            ->all()
            ->extract('id')
            ->toList();

        $this->assertSame([1, 3], $results);
    }

    private function issues(): IssuesTable
    {
        /** @var \App\Model\Table\IssuesTable */
        return $this->getTableLocator()->get(IssuesTable::class);
    }
}
