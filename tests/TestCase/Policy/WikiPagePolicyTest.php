<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\WikiPage;
use App\Policy\WikiPagePolicy;
use App\Test\Support\Auth\TestAuthorizationIdentity;
use Cake\TestSuite\TestCase;

class WikiPagePolicyTest extends TestCase
{
    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Projects',
        'app.ProjectMembers',
    ];

    public function testMembersMayManageWikiPagesButOnlyAdminsMayDelete(): void
    {
        $policy = new WikiPagePolicy();
        $wikiPage = new WikiPage(['project_id' => 1]);

        $this->assertTrue($policy->canAdd(new TestAuthorizationIdentity(['id' => 2]), $wikiPage));
        $this->assertTrue($policy->canView(new TestAuthorizationIdentity(['id' => 2]), $wikiPage));
        $this->assertTrue($policy->canEdit(new TestAuthorizationIdentity(['id' => 2]), $wikiPage));
        $this->assertFalse($policy->canDelete(new TestAuthorizationIdentity(['id' => 2]), $wikiPage));
        $this->assertTrue($policy->canDelete(new TestAuthorizationIdentity(['id' => 1]), $wikiPage));
    }
}
