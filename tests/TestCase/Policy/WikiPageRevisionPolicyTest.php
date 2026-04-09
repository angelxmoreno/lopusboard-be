<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\WikiPageRevision;
use App\Policy\WikiPageRevisionPolicy;
use App\Test\Support\Auth\TestAuthorizationIdentity;
use Cake\TestSuite\TestCase;

class WikiPageRevisionPolicyTest extends TestCase
{
    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Projects',
        'app.ProjectMembers',
        'app.WikiPages',
    ];

    public function testMembersMayViewWikiPageRevisionsFromTheirProjects(): void
    {
        $policy = new WikiPageRevisionPolicy();
        $revision = new WikiPageRevision(['wiki_page_id' => 1]);

        $this->assertTrue($policy->canView(new TestAuthorizationIdentity(['id' => 2]), $revision));
        $this->assertFalse($policy->canView(new TestAuthorizationIdentity(['id' => 1]), new WikiPageRevision([
            'wiki_page_id' => 2,
        ])));
    }
}
