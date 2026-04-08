<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\Project;
use App\Policy\ProjectPolicy;
use App\Test\Support\Auth\TestAuthorizationIdentity;
use Cake\TestSuite\TestCase;

class ProjectPolicyTest extends TestCase
{
    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Projects',
        'app.ProjectMembers',
    ];

    public function testCanAddRequiresAuthenticatedUser(): void
    {
        $policy = new ProjectPolicy();
        $project = new Project();

        $this->assertFalse($policy->canAdd(new TestAuthorizationIdentity([]), $project));
        $this->assertTrue($policy->canAdd(new TestAuthorizationIdentity(['id' => 1]), $project));
    }

    public function testCanViewRequiresProjectMembership(): void
    {
        $policy = new ProjectPolicy();
        $project = new Project(['id' => 1]);

        $this->assertTrue($policy->canView(new TestAuthorizationIdentity(['id' => 2]), $project));
        $this->assertFalse($policy->canView(new TestAuthorizationIdentity(['id' => 999]), $project));
    }

    public function testCanEditAndDeleteRequireProjectAdminRole(): void
    {
        $policy = new ProjectPolicy();
        $project = new Project(['id' => 1]);

        $this->assertTrue($policy->canEdit(new TestAuthorizationIdentity(['id' => 1]), $project));
        $this->assertTrue($policy->canDelete(new TestAuthorizationIdentity(['id' => 1]), $project));
        $this->assertFalse($policy->canEdit(new TestAuthorizationIdentity(['id' => 2]), $project));
        $this->assertFalse($policy->canDelete(new TestAuthorizationIdentity(['id' => 2]), $project));
    }
}
