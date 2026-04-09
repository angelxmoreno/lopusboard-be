<?php
declare(strict_types=1);

namespace App\Policy;

use App\Authorization\ProjectAuthorization;
use App\Model\Entity\Comment;
use Authorization\IdentityInterface;

/**
 * Comment entity policy.
 */
class CommentPolicy
{
    /**
     * @param \App\Authorization\ProjectAuthorization|null $authorization Shared project authorization helper.
     */
    public function __construct(private readonly ?ProjectAuthorization $authorization = null)
    {
    }

    /**
     * Project members may add comments to issues in their projects.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Comment $comment The comment being created.
     * @return bool
     */
    public function canAdd(IdentityInterface $user, Comment $comment): bool
    {
        return $this->authorization()->isIssueResourceProjectMember($user, $comment);
    }

    /**
     * Project members may view comments in their issue projects.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Comment $comment The comment being viewed.
     * @return bool
     */
    public function canView(IdentityInterface $user, Comment $comment): bool
    {
        return $this->authorization()->isIssueResourceProjectMember($user, $comment);
    }

    /**
     * Comment authors and project admins may edit comments.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Comment $comment The comment being edited.
     * @return bool
     */
    public function canEdit(IdentityInterface $user, Comment $comment): bool
    {
        return $this->canMutate($user, $comment);
    }

    /**
     * Comment authors and project admins may delete comments.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Comment $comment The comment being deleted.
     * @return bool
     */
    public function canDelete(IdentityInterface $user, Comment $comment): bool
    {
        return $this->canMutate($user, $comment);
    }

    /**
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Comment $comment The comment being mutated.
     * @return bool
     */
    private function canMutate(IdentityInterface $user, Comment $comment): bool
    {
        $userId = $this->authorization()->userId($user);
        if ($userId !== null && $userId === $comment->user_id) {
            return true;
        }

        return $this->authorization()->isIssueResourceProjectAdmin($user, $comment);
    }

    /**
     * @return \App\Authorization\ProjectAuthorization
     */
    private function authorization(): ProjectAuthorization
    {
        return $this->authorization ?? new ProjectAuthorization();
    }
}
