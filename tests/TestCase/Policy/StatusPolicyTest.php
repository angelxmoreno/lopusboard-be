<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\Status;
use App\Policy\StatusPolicy;
use App\Test\Support\Auth\TestAuthorizationIdentity;
use Cake\TestSuite\TestCase;

class StatusPolicyTest extends TestCase
{
    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Projects',
        'app.ProjectMembers',
    ];

    public function testProjectMembersMayViewStatusesButOnlyAdminsMayMutate(): void
    {
        $policy = new StatusPolicy();
        $status = new Status(['project_id' => 1]);

        $this->assertTrue($policy->canView(new TestAuthorizationIdentity(['id' => 2]), $status));
        $this->assertFalse($policy->canAdd(new TestAuthorizationIdentity(['id' => 2]), $status));
        $this->assertFalse($policy->canEdit(new TestAuthorizationIdentity(['id' => 2]), $status));
        $this->assertFalse($policy->canDelete(new TestAuthorizationIdentity(['id' => 2]), $status));
        $this->assertTrue($policy->canAdd(new TestAuthorizationIdentity(['id' => 1]), $status));
    }
}
