<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\Comment;
use App\Policy\CommentPolicy;
use App\Test\Support\Auth\TestAuthorizationIdentity;
use Cake\TestSuite\TestCase;

class CommentPolicyTest extends TestCase
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

    public function testMembersMayViewAndCreateCommentsWhileAuthorsOrAdminsMayMutate(): void
    {
        $policy = new CommentPolicy();
        $comment = new Comment(['issue_id' => 1, 'user_id' => 2]);

        $this->assertTrue($policy->canAdd(new TestAuthorizationIdentity(['id' => 2]), $comment));
        $this->assertTrue($policy->canView(new TestAuthorizationIdentity(['id' => 2]), $comment));
        $this->assertTrue($policy->canEdit(new TestAuthorizationIdentity(['id' => 2]), $comment));
        $this->assertTrue($policy->canDelete(new TestAuthorizationIdentity(['id' => 2]), $comment));
        $this->assertFalse($policy->canEdit(new TestAuthorizationIdentity(['id' => 3]), $comment));
        $this->assertTrue($policy->canDelete(new TestAuthorizationIdentity(['id' => 1]), $comment));
    }
}
