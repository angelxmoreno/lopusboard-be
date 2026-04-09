<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\Issue;
use App\Policy\IssuePolicy;
use App\Test\Support\Auth\TestAuthorizationIdentity;
use Cake\TestSuite\TestCase;

class IssuePolicyTest extends TestCase
{
    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Projects',
        'app.ProjectMembers',
    ];

    public function testMembersMayViewAndEditIssuesButOnlyAdminsMayDelete(): void
    {
        $policy = new IssuePolicy();
        $issue = new Issue(['project_id' => 1]);

        $this->assertTrue($policy->canAdd(new TestAuthorizationIdentity(['id' => 2]), $issue));
        $this->assertTrue($policy->canView(new TestAuthorizationIdentity(['id' => 2]), $issue));
        $this->assertTrue($policy->canEdit(new TestAuthorizationIdentity(['id' => 2]), $issue));
        $this->assertFalse($policy->canDelete(new TestAuthorizationIdentity(['id' => 2]), $issue));
        $this->assertTrue($policy->canDelete(new TestAuthorizationIdentity(['id' => 1]), $issue));
    }
}
