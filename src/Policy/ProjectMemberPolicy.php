<?php
declare(strict_types=1);

namespace App\Policy;

use App\Authorization\ProjectAuthorization;
use App\Model\Entity\ProjectMember;
use Authorization\IdentityInterface;

/**
 * Project member entity policy.
 */
class ProjectMemberPolicy
{
    /**
     * @param \App\Authorization\ProjectAuthorization|null $authorization Shared project authorization helper.
     */
    public function __construct(private readonly ?ProjectAuthorization $authorization = null)
    {
    }

    /**
     * Only project admins may add memberships to their projects.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\ProjectMember $projectMember The membership being created.
     * @return bool
     */
    public function canAdd(IdentityInterface $user, ProjectMember $projectMember): bool
    {
        return $this->authorization()->isResourceProjectAdmin($user, $projectMember);
    }

    /**
     * Only project admins may view membership records.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\ProjectMember $projectMember The membership being viewed.
     * @return bool
     */
    public function canView(IdentityInterface $user, ProjectMember $projectMember): bool
    {
        return $this->authorization()->isResourceProjectAdmin($user, $projectMember);
    }

    /**
     * Only project admins may edit membership records.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\ProjectMember $projectMember The membership being edited.
     * @return bool
     */
    public function canEdit(IdentityInterface $user, ProjectMember $projectMember): bool
    {
        return $this->authorization()->isResourceProjectAdmin($user, $projectMember);
    }

    /**
     * Only project admins may delete membership records.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\ProjectMember $projectMember The membership being deleted.
     * @return bool
     */
    public function canDelete(IdentityInterface $user, ProjectMember $projectMember): bool
    {
        return $this->authorization()->isResourceProjectAdmin($user, $projectMember);
    }

    /**
     * @return \App\Authorization\ProjectAuthorization
     */
    private function authorization(): ProjectAuthorization
    {
        return $this->authorization ?? new ProjectAuthorization();
    }
}
