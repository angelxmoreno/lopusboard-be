<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Table\WikiPageRevisionsTable;
use App\Policy\WikiPageRevisionsTablePolicy;
use App\Test\Support\Auth\TestAuthorizationIdentity;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;

class WikiPageRevisionsTablePolicyTest extends TestCase
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
        'app.WikiPageRevisions',
    ];

    public function testScopeIndexRestrictsResultsToAccessibleProjects(): void
    {
        $policy = new WikiPageRevisionsTablePolicy();

        $results = $policy
            ->scopeIndex(new TestAuthorizationIdentity(['id' => 2]), $this->wikiPageRevisions()->find())
            ->all()
            ->extract('id')
            ->toList();

        $this->assertSame([1], $results);
    }

    private function wikiPageRevisions(): WikiPageRevisionsTable
    {
        /** @var \App\Model\Table\WikiPageRevisionsTable */
        return $this->getTableLocator()->get(WikiPageRevisionsTable::class);
    }
}
