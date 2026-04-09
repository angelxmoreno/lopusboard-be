<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\Department;
use App\Policy\DepartmentPolicy;
use App\Test\Support\Auth\TestAuthorizationIdentity;
use Cake\TestSuite\TestCase;

class DepartmentPolicyTest extends TestCase
{
    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Projects',
        'app.ProjectMembers',
    ];

    public function testProjectMembersMayViewDepartmentsButOnlyAdminsMayMutate(): void
    {
        $policy = new DepartmentPolicy();
        $department = new Department(['project_id' => 1]);

        $this->assertTrue($policy->canView(new TestAuthorizationIdentity(['id' => 2]), $department));
        $this->assertFalse($policy->canAdd(new TestAuthorizationIdentity(['id' => 2]), $department));
        $this->assertFalse($policy->canEdit(new TestAuthorizationIdentity(['id' => 2]), $department));
        $this->assertFalse($policy->canDelete(new TestAuthorizationIdentity(['id' => 2]), $department));
        $this->assertTrue($policy->canAdd(new TestAuthorizationIdentity(['id' => 1]), $department));
    }
}
