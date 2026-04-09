<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Table\WikiPagesTable;
use App\Policy\WikiPagesTablePolicy;
use App\Test\Support\Auth\TestAuthorizationIdentity;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;

class WikiPagesTablePolicyTest extends TestCase
{
    use LocatorAwareTrait;

    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Projects',
        'app.ProjectMembers',
        'app.WikiPages',
    ];

    public function testScopeIndexRestrictsResultsToAccessibleProjects(): void
    {
        $policy = new WikiPagesTablePolicy();

        $results = $policy
            ->scopeIndex(new TestAuthorizationIdentity(['id' => 2]), $this->wikiPages()->find())
            ->all()
            ->extract('id')
            ->toList();

        sort($results);

        $this->assertSame([1, 3], $results);
    }

    private function wikiPages(): WikiPagesTable
    {
        /** @var \App\Model\Table\WikiPagesTable */
        return $this->getTableLocator()->get(WikiPagesTable::class);
    }
}
