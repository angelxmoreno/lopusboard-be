<?php
declare(strict_types=1);

namespace App\Authorization;

use App\Model\Entity\Project;
use App\Model\Enum\ProjectMemberRole;
use App\Model\Table\IssuesTable;
use App\Model\Table\ProjectMembersTable;
use Authorization\IdentityInterface;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Query\SelectQuery;

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
     * Returns whether the identity belongs to the resource's project.
     *
     * @param \Authorization\IdentityInterface $user The authenticated identity.
     * @param object $resource The project-scoped resource.
     * @return bool
     */
    public function isResourceProjectMember(IdentityInterface $user, object $resource): bool
    {
        $projectId = $this->resourceProjectId($resource);
        if ($projectId === null) {
            return $this->isAuthenticated($user);
        }

        return $this->isProjectMember($user, $projectId);
    }

    /**
     * Returns whether the identity is an admin for the resource's project.
     *
     * @param \Authorization\IdentityInterface $user The authenticated identity.
     * @param object $resource The project-scoped resource.
     * @return bool
     */
    public function isResourceProjectAdmin(IdentityInterface $user, object $resource): bool
    {
        $projectId = $this->resourceProjectId($resource);
        if ($projectId === null) {
            return $this->isAuthenticated($user);
        }

        return $this->isProjectAdmin($user, $projectId);
    }

    /**
     * Returns whether the identity belongs to the resource's issue project.
     *
     * @param \Authorization\IdentityInterface $user The authenticated identity.
     * @param object $resource The issue-scoped resource.
     * @param string $issueField The issue id field on the resource.
     * @return bool
     */
    public function isIssueResourceProjectMember(
        IdentityInterface $user,
        object $resource,
        string $issueField = 'issue_id',
    ): bool {
        $issueId = $this->resourceIssueId($resource, $issueField);
        if ($issueId === null) {
            return $this->isAuthenticated($user);
        }

        return $this->isIssueProjectMember($user, $issueId);
    }

    /**
     * Returns whether the identity is an admin for the resource's issue project.
     *
     * @param \Authorization\IdentityInterface $user The authenticated identity.
     * @param object $resource The issue-scoped resource.
     * @param string $issueField The issue id field on the resource.
     * @return bool
     */
    public function isIssueResourceProjectAdmin(
        IdentityInterface $user,
        object $resource,
        string $issueField = 'issue_id',
    ): bool {
        $issueId = $this->resourceIssueId($resource, $issueField);
        if ($issueId === null) {
            return $this->isAuthenticated($user);
        }

        return $this->isIssueProjectAdmin($user, $issueId);
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
     * Applies a project-membership scope to the provided query.
     *
     * @param \Authorization\IdentityInterface $user The authenticated identity.
     * @template TSubject of \Cake\Datasource\EntityInterface
     * @param \Cake\ORM\Query\SelectQuery<TSubject> $query The query to scope.
     * @param string $projectField The project id field on the target query.
     * @param bool $adminOnly Whether only admin memberships should match.
     * @return \Cake\ORM\Query\SelectQuery<TSubject>
     */
    public function scopeToAccessibleProjects(
        IdentityInterface $user,
        SelectQuery $query,
        string $projectField = 'project_id',
        bool $adminOnly = false,
    ): SelectQuery {
        $projectIds = $adminOnly
            ? $this->adminProjectIds($user)
            : $this->memberProjectIds($user);

        if ($projectIds === []) {
            return $query->where([$projectField . ' IS' => null]);
        }

        return $query->where([$projectField . ' IN' => $projectIds]);
    }

    /**
     * Returns whether the identity belongs to the given issue's project.
     *
     * @param \Authorization\IdentityInterface $user The authenticated identity.
     * @param int $issueId The issue id.
     * @return bool
     */
    public function isIssueProjectMember(IdentityInterface $user, int $issueId): bool
    {
        $projectId = $this->issueProjectId($issueId);
        if ($projectId === null) {
            return false;
        }

        return $this->isProjectMember($user, $projectId);
    }

    /**
     * Returns whether the identity administers the given issue's project.
     *
     * @param \Authorization\IdentityInterface $user The authenticated identity.
     * @param int $issueId The issue id.
     * @return bool
     */
    public function isIssueProjectAdmin(IdentityInterface $user, int $issueId): bool
    {
        $projectId = $this->issueProjectId($issueId);
        if ($projectId === null) {
            return false;
        }

        return $this->isProjectAdmin($user, $projectId);
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
     * Returns the project id from a project-scoped resource.
     *
     * @param object $resource The project-scoped resource.
     * @return int|null
     */
    private function resourceProjectId(object $resource): ?int
    {
        $projectId = $resource->project_id ?? null;

        if (is_int($projectId)) {
            return $projectId;
        }
        if (is_string($projectId) && ctype_digit($projectId)) {
            return (int)$projectId;
        }

        return null;
    }

    /**
     * Returns the issue id from an issue-scoped resource.
     *
     * @param object $resource The issue-scoped resource.
     * @param string $field The issue id field on the resource.
     * @return int|null
     */
    private function resourceIssueId(object $resource, string $field): ?int
    {
        $issueId = $resource->{$field} ?? null;

        if (is_int($issueId)) {
            return $issueId;
        }
        if (is_string($issueId) && ctype_digit($issueId)) {
            return (int)$issueId;
        }

        return null;
    }

    /**
     * Returns the project id for a given issue.
     *
     * @param int $issueId The issue id.
     * @return int|null
     */
    private function issueProjectId(int $issueId): ?int
    {
        $issue = $this->issues()
            ->find()
            ->select(['project_id'])
            ->where(['Issues.id' => $issueId])
            ->disableHydration()
            ->first();

        return $issue === null ? null : (int)$issue['project_id'];
    }

    /**
     * Returns project ids the user belongs to.
     *
     * @param \Authorization\IdentityInterface $user The authenticated identity.
     * @return list<int>
     */
    private function memberProjectIds(IdentityInterface $user): array
    {
        return $this->projectIdsFor($user);
    }

    /**
     * Returns project ids where the user is an admin.
     *
     * @param \Authorization\IdentityInterface $user The authenticated identity.
     * @return list<int>
     */
    private function adminProjectIds(IdentityInterface $user): array
    {
        return $this->projectIdsFor($user, ProjectMemberRole::Admin);
    }

    /**
     * Returns project ids for the current user, optionally filtered by role.
     *
     * @param \Authorization\IdentityInterface $user The authenticated identity.
     * @param \App\Model\Enum\ProjectMemberRole|null $role Optional membership role filter.
     * @return list<int>
     */
    private function projectIdsFor(IdentityInterface $user, ?ProjectMemberRole $role = null): array
    {
        $userId = $this->userId($user);
        if ($userId === null) {
            return [];
        }

        $conditions = ['user_id' => $userId];
        if ($role !== null) {
            $conditions['role'] = $role->value;
        }

        /** @var list<int> $projectIds */
        $projectIds = $this->projectMembers()
            ->find()
            ->select(['project_id'])
            ->where($conditions)
            ->disableHydration()
            ->all()
            ->extract('project_id')
            ->map(static fn(mixed $projectId): int => (int)$projectId)
            ->toList();

        return $projectIds;
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

    /**
     * Returns the issues table instance.
     *
     * @return \App\Model\Table\IssuesTable
     */
    private function issues(): IssuesTable
    {
        /** @var \App\Model\Table\IssuesTable */
        return $this->getTableLocator()->get(IssuesTable::class);
    }
}
