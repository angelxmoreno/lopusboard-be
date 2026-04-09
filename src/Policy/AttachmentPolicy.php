<?php
declare(strict_types=1);

namespace App\Policy;

use App\Authorization\ProjectAuthorization;
use App\Model\Entity\Attachment;
use Authorization\IdentityInterface;

/**
 * Attachment entity policy.
 */
class AttachmentPolicy
{
    /**
     * @param \App\Authorization\ProjectAuthorization|null $authorization Shared project authorization helper.
     */
    public function __construct(private readonly ?ProjectAuthorization $authorization = null)
    {
    }

    /**
     * Project members may add attachments to their projects.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Attachment $attachment The attachment being created.
     * @return bool
     */
    public function canAdd(IdentityInterface $user, Attachment $attachment): bool
    {
        return $this->authorization()->isResourceProjectMember($user, $attachment);
    }

    /**
     * Project members may view attachments in their projects.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Attachment $attachment The attachment being viewed.
     * @return bool
     */
    public function canView(IdentityInterface $user, Attachment $attachment): bool
    {
        return $this->authorization()->isResourceProjectMember($user, $attachment);
    }

    /**
     * Only project admins may edit attachments.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Attachment $attachment The attachment being edited.
     * @return bool
     */
    public function canEdit(IdentityInterface $user, Attachment $attachment): bool
    {
        return $this->authorization()->isResourceProjectAdmin($user, $attachment);
    }

    /**
     * Only project admins may delete attachments.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Attachment $attachment The attachment being deleted.
     * @return bool
     */
    public function canDelete(IdentityInterface $user, Attachment $attachment): bool
    {
        return $this->authorization()->isResourceProjectAdmin($user, $attachment);
    }

    /**
     * @return \App\Authorization\ProjectAuthorization
     */
    private function authorization(): ProjectAuthorization
    {
        return $this->authorization ?? new ProjectAuthorization();
    }
}
