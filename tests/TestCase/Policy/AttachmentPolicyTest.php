<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\Attachment;
use App\Policy\AttachmentPolicy;
use App\Test\Support\Auth\TestAuthorizationIdentity;
use Cake\TestSuite\TestCase;

class AttachmentPolicyTest extends TestCase
{
    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Projects',
        'app.ProjectMembers',
    ];

    public function testMembersMayViewAttachmentsButOnlyAdminsMayMutateThem(): void
    {
        $policy = new AttachmentPolicy();
        $attachment = new Attachment(['project_id' => 1]);

        $this->assertTrue($policy->canAdd(new TestAuthorizationIdentity(['id' => 2]), $attachment));
        $this->assertTrue($policy->canView(new TestAuthorizationIdentity(['id' => 2]), $attachment));
        $this->assertFalse($policy->canEdit(new TestAuthorizationIdentity(['id' => 2]), $attachment));
        $this->assertFalse($policy->canDelete(new TestAuthorizationIdentity(['id' => 2]), $attachment));
        $this->assertTrue($policy->canEdit(new TestAuthorizationIdentity(['id' => 1]), $attachment));
        $this->assertTrue($policy->canDelete(new TestAuthorizationIdentity(['id' => 1]), $attachment));
    }
}
