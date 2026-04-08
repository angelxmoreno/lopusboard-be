<?php
declare(strict_types=1);

namespace App\Authorization;

use App\Model\Entity\Project;
use App\Model\Enum\ProjectMemberRole;
use App\Model\Table\ProjectMembersTable;
use Authorization\IdentityInterface;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * Shared authorization helpers for project-scoped access rules.
 */
class ProjectAuthorization
{
    use LocatorAwareTrait;

    /**
     * Returns whether the current identity has a usable local user id.
     *
     * @param \Authorization\IdentityInterface $user The authenticated identity.
     * @return bool
     */
    public function isAuthenticated(IdentityInterface $user): bool
    {
        return $this->userId($user) !== null;
    }

    /**
     * Returns whether the identity is a member of the project.
     *
     * @param \Authorization\IdentityInterface $user The authenticated identity.
     * @param \App\Model\Entity\Project|int $project The project entity or id.
     * @return bool
     */
    public function isProjectMember(IdentityInterface $user, Project|int $project): bool
    {
        $userId = $this->userId($user);
        if ($userId === null) {
            return false;
        }

        return $this->projectMembers()->exists([
            'project_id' => $this->projectId($project),
            'user_id' => $userId,
        ]);
    }

    /**
     * Returns whether the identity is an admin member of the project.
     *
     * @param \Authorization\IdentityInterface $user The authenticated identity.
     * @param \App\Model\Entity\Project|int $project The project entity or id.
     * @return bool
     */
    public function isProjectAdmin(IdentityInterface $user, Project|int $project): bool
    {
        $userId = $this->userId($user);
        if ($userId === null) {
            return false;
        }

        return $this->projectMembers()->exists([
            'project_id' => $this->projectId($project),
            'user_id' => $userId,
            'role' => ProjectMemberRole::Admin->value,
        ]);
    }

    /**
     * Returns the authenticated user id from the identity.
     *
     * @param \Authorization\IdentityInterface $user The authenticated identity.
     * @return int|null
     */
    public function userId(IdentityInterface $user): ?int
    {
        $userId = $user['id'] ?? null;
        if (is_int($userId)) {
            return $userId;
        }
        if (is_string($userId) && ctype_digit($userId)) {
            return (int)$userId;
        }

        return null;
    }

    /**
     * Returns the project id from either an entity or an integer.
     *
     * @param \App\Model\Entity\Project|int $project The project entity or id.
     * @return int
     */
    private function projectId(Project|int $project): int
    {
        return is_int($project) ? $project : $project->id;
    }

    /**
     * Returns the project members table instance.
     *
     * @return \App\Model\Table\ProjectMembersTable
     */
    private function projectMembers(): ProjectMembersTable
    {
        /** @var \App\Model\Table\ProjectMembersTable */
        return $this->getTableLocator()->get(ProjectMembersTable::class);
    }
}
