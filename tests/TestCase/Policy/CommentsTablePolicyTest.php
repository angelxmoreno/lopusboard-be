<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Table\CommentsTable;
use App\Policy\CommentsTablePolicy;
use App\Test\Support\Auth\TestAuthorizationIdentity;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;

class CommentsTablePolicyTest extends TestCase
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
        'app.Comments',
    ];

    public function testScopeIndexRestrictsCommentsToAccessibleIssueProjects(): void
    {
        $policy = new CommentsTablePolicy();

        $results = $policy
            ->scopeIndex(new TestAuthorizationIdentity(['id' => 2]), $this->comments()->find())
            ->all()
            ->extract('id')
            ->toList();

        $this->assertSame([1], $results);
    }

    private function comments(): CommentsTable
    {
        /** @var \App\Model\Table\CommentsTable */
        return $this->getTableLocator()->get(CommentsTable::class);
    }
}
