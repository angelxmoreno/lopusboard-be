<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Table\IssueRelationsTable;
use App\Policy\IssueRelationsTablePolicy;
use App\Test\Support\Auth\TestAuthorizationIdentity;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;

class IssueRelationsTablePolicyTest extends TestCase
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
        'app.IssueRelations',
    ];

    public function testScopeIndexRestrictsRelationsToAccessibleIssueProjects(): void
    {
        $policy = new IssueRelationsTablePolicy();

        $results = $policy
            ->scopeIndex(new TestAuthorizationIdentity(['id' => 2]), $this->issueRelations()->find())
            ->all()
            ->extract('id')
            ->toList();

        $this->assertSame([1], $results);
    }

    private function issueRelations(): IssueRelationsTable
    {
        /** @var \App\Model\Table\IssueRelationsTable */
        return $this->getTableLocator()->get(IssueRelationsTable::class);
    }
}
