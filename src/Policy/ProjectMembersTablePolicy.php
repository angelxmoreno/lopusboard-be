<?php
declare(strict_types=1);

namespace App\Policy;

use App\Authorization\ProjectAuthorization;
use Authorization\IdentityInterface;
use Cake\ORM\Query\SelectQuery;

/**
 * Project members table policy.
 */
class ProjectMembersTablePolicy
{
    /**
     * @param \App\Authorization\ProjectAuthorization|null $authorization Shared project authorization helper.
     */
    public function __construct(private readonly ?ProjectAuthorization $authorization = null)
    {
    }

    /**
     * Any authenticated user may access the members index, but results are scoped
     * down to projects they administer.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @return bool
     */
    public function canIndex(IdentityInterface $user): bool
    {
        return $this->authorization()->isAuthenticated($user);
    }

    /**
     * Any authenticated user may attempt to add a member. The entity policy
     * enforces the project-admin requirement once the target project is known.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @return bool
     */
    public function canAdd(IdentityInterface $user): bool
    {
        return $this->authorization()->isAuthenticated($user);
    }

    /**
     * Restrict index queries to projects the current user administers.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @template TSubject of \Cake\Datasource\EntityInterface
     * @param \Cake\ORM\Query\SelectQuery<TSubject> $query The query to scope.
     * @return \Cake\ORM\Query\SelectQuery<TSubject>
     */
    public function scopeIndex(IdentityInterface $user, SelectQuery $query): SelectQuery
    {
        return $this->authorization()->scopeToAdminProjects(
            $user,
            $query,
            'ProjectMembers.project_id',
        );
    }

    /**
     * @return \App\Authorization\ProjectAuthorization
     */
    private function authorization(): ProjectAuthorization
    {
        return $this->authorization ?? new ProjectAuthorization();
    }
}
