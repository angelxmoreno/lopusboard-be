<?php
declare(strict_types=1);

namespace App\Test\TestCase\Authorization;

use App\Authorization\ProjectAuthorization;
use App\Authorization\RelatedProjectAuthorization;
use App\Model\Entity\Project;
use App\Model\Table\ProjectsTable;
use App\Test\Support\Auth\TestAuthorizationIdentity;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;

class ProjectAuthorizationTest extends TestCase
{
    use LocatorAwareTrait;

    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Projects',
        'app.ProjectMembers',
        'app.WikiPages',
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

    public function testIsResourceProjectAdminReturnsFalseWhenProjectCannotBeResolved(): void
    {
        $authorization = new ProjectAuthorization();

        $this->assertFalse($authorization->isResourceProjectAdmin(
            new TestAuthorizationIdentity(['id' => 1]),
            (object)[],
        ));
    }

    public function testScopeToAccessibleProjectsReturnsNoRowsForUsersWithoutMemberships(): void
    {
        $authorization = new ProjectAuthorization();

        $results = $authorization
            ->scopeToAccessibleProjects(
                new TestAuthorizationIdentity(['id' => 999]),
                $this->projects()->find(),
                'Projects.id',
            )
            ->all()
            ->extract('id')
            ->toList();

        $this->assertSame([], $results);
    }

    public function testRelatedAuthorizationChecksWikiPageProjectMembership(): void
    {
        $authorization = new RelatedProjectAuthorization(new ProjectAuthorization());

        $this->assertTrue($authorization->isWikiPageProjectMember(
            new TestAuthorizationIdentity(['id' => 2]),
            1,
        ));
        $this->assertFalse($authorization->isWikiPageProjectMember(
            new TestAuthorizationIdentity(['id' => 1]),
            2,
        ));
    }

    private function projects(): ProjectsTable
    {
        /** @var \App\Model\Table\ProjectsTable */
        return $this->getTableLocator()->get(ProjectsTable::class);
    }
}
