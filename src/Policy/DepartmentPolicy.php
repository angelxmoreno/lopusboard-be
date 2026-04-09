<?php
declare(strict_types=1);

namespace App\Policy;

use App\Authorization\ProjectAuthorization;
use App\Model\Entity\Department;
use Authorization\IdentityInterface;

/**
 * Department entity policy.
 */
class DepartmentPolicy
{
    /**
     * @param \App\Authorization\ProjectAuthorization|null $authorization Shared project authorization helper.
     */
    public function __construct(private readonly ?ProjectAuthorization $authorization = null)
    {
    }

    /**
     * Only project admins may add departments.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Department $department The department being created.
     * @return bool
     */
    public function canAdd(IdentityInterface $user, Department $department): bool
    {
        return $this->authorization()->isResourceProjectAdmin($user, $department);
    }

    /**
     * Project members may view departments for their projects.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Department $department The department being viewed.
     * @return bool
     */
    public function canView(IdentityInterface $user, Department $department): bool
    {
        return $this->authorization()->isResourceProjectMember($user, $department);
    }

    /**
     * Only project admins may edit departments.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Department $department The department being edited.
     * @return bool
     */
    public function canEdit(IdentityInterface $user, Department $department): bool
    {
        return $this->authorization()->isResourceProjectAdmin($user, $department);
    }

    /**
     * Only project admins may delete departments.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Department $department The department being deleted.
     * @return bool
     */
    public function canDelete(IdentityInterface $user, Department $department): bool
    {
        return $this->authorization()->isResourceProjectAdmin($user, $department);
    }

    /**
     * @return \App\Authorization\ProjectAuthorization
     */
    private function authorization(): ProjectAuthorization
    {
        return $this->authorization ?? new ProjectAuthorization();
    }
}
