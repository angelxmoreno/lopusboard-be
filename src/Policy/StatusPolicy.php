<?php
declare(strict_types=1);

namespace App\Policy;

use App\Authorization\ProjectAuthorization;
use App\Model\Entity\Status;
use Authorization\IdentityInterface;

/**
 * Status entity policy.
 */
class StatusPolicy
{
    /**
     * @param \App\Authorization\ProjectAuthorization|null $authorization Shared project authorization helper.
     */
    public function __construct(private readonly ?ProjectAuthorization $authorization = null)
    {
    }

    /**
     * Only project admins may add statuses to a project.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Status $status The status being created.
     * @return bool
     */
    public function canAdd(IdentityInterface $user, Status $status): bool
    {
        return $this->authorization()->isResourceProjectAdmin($user, $status);
    }

    /**
     * Project members may view statuses for their projects.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Status $status The status being viewed.
     * @return bool
     */
    public function canView(IdentityInterface $user, Status $status): bool
    {
        return $this->authorization()->isResourceProjectMember($user, $status);
    }

    /**
     * Only project admins may edit statuses.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Status $status The status being edited.
     * @return bool
     */
    public function canEdit(IdentityInterface $user, Status $status): bool
    {
        return $this->authorization()->isResourceProjectAdmin($user, $status);
    }

    /**
     * Only project admins may delete statuses.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Status $status The status being deleted.
     * @return bool
     */
    public function canDelete(IdentityInterface $user, Status $status): bool
    {
        return $this->authorization()->isResourceProjectAdmin($user, $status);
    }

    /**
     * @return \App\Authorization\ProjectAuthorization
     */
    private function authorization(): ProjectAuthorization
    {
        return $this->authorization ?? new ProjectAuthorization();
    }
}
