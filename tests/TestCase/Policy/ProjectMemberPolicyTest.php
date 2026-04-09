<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\ProjectMember;
use App\Policy\ProjectMemberPolicy;
use App\Test\Support\Auth\TestAuthorizationIdentity;
use Cake\TestSuite\TestCase;

class ProjectMemberPolicyTest extends TestCase
{
    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Projects',
        'app.ProjectMembers',
    ];

    public function testProjectAdminMayManageMemberships(): void
    {
        $policy = new ProjectMemberPolicy();
        $projectMember = new ProjectMember(['project_id' => 1]);

        $this->assertTrue($policy->canAdd(new TestAuthorizationIdentity(['id' => 1]), $projectMember));
        $this->assertTrue($policy->canView(new TestAuthorizationIdentity(['id' => 1]), $projectMember));
        $this->assertTrue($policy->canEdit(new TestAuthorizationIdentity(['id' => 1]), $projectMember));
        $this->assertTrue($policy->canDelete(new TestAuthorizationIdentity(['id' => 1]), $projectMember));
    }

    public function testNonAdminCannotManageMemberships(): void
    {
        $policy = new ProjectMemberPolicy();
        $projectMember = new ProjectMember(['project_id' => 1]);

        $this->assertFalse($policy->canAdd(new TestAuthorizationIdentity(['id' => 2]), $projectMember));
        $this->assertFalse($policy->canView(new TestAuthorizationIdentity(['id' => 2]), $projectMember));
        $this->assertFalse($policy->canEdit(new TestAuthorizationIdentity(['id' => 2]), $projectMember));
        $this->assertFalse($policy->canDelete(new TestAuthorizationIdentity(['id' => 2]), $projectMember));
    }
}
