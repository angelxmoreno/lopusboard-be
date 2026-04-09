<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\IssueRelation;
use App\Policy\IssueRelationPolicy;
use App\Test\Support\Auth\TestAuthorizationIdentity;
use Cake\TestSuite\TestCase;

class IssueRelationPolicyTest extends TestCase
{
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

    public function testMembersMayManageIssueRelationsWithinTheirProjects(): void
    {
        $policy = new IssueRelationPolicy();
        $relation = new IssueRelation(['issue_id' => 1]);

        $this->assertTrue($policy->canAdd(new TestAuthorizationIdentity(['id' => 2]), $relation));
        $this->assertTrue($policy->canView(new TestAuthorizationIdentity(['id' => 2]), $relation));
        $this->assertTrue($policy->canEdit(new TestAuthorizationIdentity(['id' => 2]), $relation));
        $this->assertTrue($policy->canDelete(new TestAuthorizationIdentity(['id' => 2]), $relation));
    }
}
