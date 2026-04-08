<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Table\ProjectsTable;
use App\Policy\ProjectsTablePolicy;
use App\Test\Support\Auth\TestAuthorizationIdentity;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;

class ProjectsTablePolicyTest extends TestCase
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

    public function testCanIndexRequiresAuthenticatedUser(): void
    {
        $policy = new ProjectsTablePolicy();

        $this->assertFalse($policy->canIndex(new TestAuthorizationIdentity([])));
        $this->assertTrue($policy->canIndex(new TestAuthorizationIdentity(['id' => 1])));
    }

    public function testCanAddRequiresAuthenticatedUser(): void
    {
        $policy = new ProjectsTablePolicy();

        $this->assertFalse($policy->canAdd(new TestAuthorizationIdentity([])));
        $this->assertTrue($policy->canAdd(new TestAuthorizationIdentity(['id' => 1])));
    }

    public function testScopeIndexRestrictsResultsToUserProjects(): void
    {
        $policy = new ProjectsTablePolicy();
        $query = $this->projects()->find();

        $results = $policy
            ->scopeIndex(new TestAuthorizationIdentity(['id' => 2]), $query)
            ->all()
            ->extract('id')
            ->toList();

        $this->assertSame([1], $results);
    }

    private function projects(): ProjectsTable
    {
        /** @var \App\Model\Table\ProjectsTable */
        return $this->getTableLocator()->get(ProjectsTable::class);
    }
}
