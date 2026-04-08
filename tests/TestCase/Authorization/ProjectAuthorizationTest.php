<?php
declare(strict_types=1);

namespace App\Test\TestCase\Authorization;

use App\Authorization\ProjectAuthorization;
use App\Model\Entity\Project;
use App\Test\Support\Auth\TestAuthorizationIdentity;
use Cake\TestSuite\TestCase;

class ProjectAuthorizationTest extends TestCase
{
    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Projects',
        'app.ProjectMembers',
    ];

    public function testIsAuthenticatedReturnsTrueWhenIdentityHasId(): void
    {
        $authorization = new ProjectAuthorization();

        $this->assertTrue($authorization->isAuthenticated(new TestAuthorizationIdentity(['id' => 1])));
    }

    public function testIsProjectMemberReturnsTrueForProjectMember(): void
    {
        $authorization = new ProjectAuthorization();

        $this->assertTrue($authorization->isProjectMember(
            new TestAuthorizationIdentity(['id' => 2]),
            1,
        ));
    }

    public function testIsProjectAdminReturnsFalseForNonAdminMember(): void
    {
        $authorization = new ProjectAuthorization();

        $this->assertFalse($authorization->isProjectAdmin(
            new TestAuthorizationIdentity(['id' => 2]),
            new Project(['id' => 1]),
        ));
    }
}
