<?php
declare(strict_types=1);

namespace App\Policy;

use App\Authorization\ProjectAuthorization;
use Authorization\IdentityInterface;
use Cake\ORM\Query\SelectQuery;

/**
 * Attachments table policy.
 */
class AttachmentsTablePolicy
{
    /**
     * @param \App\Authorization\ProjectAuthorization|null $authorization Shared project authorization helper.
     */
    public function __construct(private readonly ?ProjectAuthorization $authorization = null)
    {
    }

    /**
     * Any authenticated user may list attachments from accessible projects.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @return bool
     */
    public function canIndex(IdentityInterface $user): bool
    {
        return $this->authorization()->isAuthenticated($user);
    }

    /**
     * Any authenticated user may attempt to add an attachment. The entity policy
     * enforces project membership once the target project is known.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @return bool
     */
    public function canAdd(IdentityInterface $user): bool
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
            'Attachments.project_id',
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
