<?php
declare(strict_types=1);

namespace App\Policy;

use App\Authorization\ProjectAuthorization;
use Authorization\IdentityInterface;
use Cake\ORM\Query\SelectQuery;

/**
 * Activity log table policy.
 */
class ActivityLogTablePolicy
{
    /**
     * @param \App\Authorization\ProjectAuthorization|null $authorization Shared project authorization helper.
     */
    public function __construct(private readonly ?ProjectAuthorization $authorization = null)
    {
    }

    /**
     * Any authenticated user may list activity feed entries from accessible projects.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @return bool
     */
    public function canIndex(IdentityInterface $user): bool
    {
        return $this->authorization()->isAuthenticated($user);
    }

    /**
     * @template TSubject of \Cake\Datasource\EntityInterface
     * @param \Cake\ORM\Query\SelectQuery<TSubject> $query
     * @return \Cake\ORM\Query\SelectQuery<TSubject>
     */
    public function scopeIndex(IdentityInterface $user, SelectQuery $query): SelectQuery
    {
        return $this->authorization()->scopeToAccessibleProjects(
            $user,
            $query,
            'ActivityLog.project_id',
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
