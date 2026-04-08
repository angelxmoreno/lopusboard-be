<?php
declare(strict_types=1);

namespace App\Policy;

use App\Authorization\ProjectAuthorization;
use App\Model\Entity\Project;
use Authorization\IdentityInterface;

/**
 * Project entity policy.
 */
class ProjectPolicy
{
    /**
     * @param \App\Authorization\ProjectAuthorization|null $authorization Shared project authorization helper.
     */
    public function __construct(private readonly ?ProjectAuthorization $authorization = null)
    {
    }

    /**
     * Any authenticated user may create a project.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Project $project The project being created.
     * @return bool
     */
    public function canAdd(IdentityInterface $user, Project $project): bool
    {
        return $this->authorization()->isAuthenticated($user);
    }

    /**
     * Only project admins may edit a project.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Project $project The project being edited.
     * @return bool
     */
    public function canEdit(IdentityInterface $user, Project $project): bool
    {
        return $this->authorization()->isProjectAdmin($user, $project);
    }

    /**
     * Only project admins may delete a project.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Project $project The project being deleted.
     * @return bool
     */
    public function canDelete(IdentityInterface $user, Project $project): bool
    {
        return $this->authorization()->isProjectAdmin($user, $project);
    }

    /**
     * Project members may view a project.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Project $project The project being viewed.
     * @return bool
     */
    public function canView(IdentityInterface $user, Project $project): bool
    {
        return $this->authorization()->isProjectMember($user, $project);
    }

    /**
     * Returns the shared project authorization helper.
     *
     * @return \App\Authorization\ProjectAuthorization
     */
    private function authorization(): ProjectAuthorization
    {
        return $this->authorization ?? new ProjectAuthorization();
    }
}
