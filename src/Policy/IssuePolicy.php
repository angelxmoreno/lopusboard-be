<?php
declare(strict_types=1);

namespace App\Policy;

use App\Authorization\ProjectAuthorization;
use App\Model\Entity\Issue;
use Authorization\IdentityInterface;

/**
 * Issue entity policy.
 */
class IssuePolicy
{
    /**
     * @param \App\Authorization\ProjectAuthorization|null $authorization Shared project authorization helper.
     */
    public function __construct(private readonly ?ProjectAuthorization $authorization = null)
    {
    }

    /**
     * Project members may add issues to their projects.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Issue $issue The issue being created.
     * @return bool
     */
    public function canAdd(IdentityInterface $user, Issue $issue): bool
    {
        return $this->authorization()->isResourceProjectMember($user, $issue);
    }

    /**
     * Project members may view issues in their projects.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Issue $issue The issue being viewed.
     * @return bool
     */
    public function canView(IdentityInterface $user, Issue $issue): bool
    {
        return $this->authorization()->isResourceProjectMember($user, $issue);
    }

    /**
     * Project members may edit issues in their projects.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Issue $issue The issue being edited.
     * @return bool
     */
    public function canEdit(IdentityInterface $user, Issue $issue): bool
    {
        return $this->authorization()->isResourceProjectMember($user, $issue);
    }

    /**
     * Only project admins may delete issues.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Issue $issue The issue being deleted.
     * @return bool
     */
    public function canDelete(IdentityInterface $user, Issue $issue): bool
    {
        return $this->authorization()->isResourceProjectAdmin($user, $issue);
    }

    /**
     * @return \App\Authorization\ProjectAuthorization
     */
    private function authorization(): ProjectAuthorization
    {
        return $this->authorization ?? new ProjectAuthorization();
    }
}
