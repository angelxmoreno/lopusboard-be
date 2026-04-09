<?php
declare(strict_types=1);

namespace App\Policy;

use App\Authorization\ProjectAuthorization;
use App\Model\Entity\WikiPage;
use Authorization\IdentityInterface;

/**
 * Wiki page entity policy.
 */
class WikiPagePolicy
{
    /**
     * @param \App\Authorization\ProjectAuthorization|null $authorization Shared project authorization helper.
     */
    public function __construct(private readonly ?ProjectAuthorization $authorization = null)
    {
    }

    /**
     * Project members may add wiki pages to their projects.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\WikiPage $wikiPage The page being created.
     * @return bool
     */
    public function canAdd(IdentityInterface $user, WikiPage $wikiPage): bool
    {
        return $this->authorization()->isResourceProjectMember($user, $wikiPage);
    }

    /**
     * Project members may view wiki pages in their projects.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\WikiPage $wikiPage The page being viewed.
     * @return bool
     */
    public function canView(IdentityInterface $user, WikiPage $wikiPage): bool
    {
        return $this->authorization()->isResourceProjectMember($user, $wikiPage);
    }

    /**
     * Project members may edit wiki pages in their projects.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\WikiPage $wikiPage The page being edited.
     * @return bool
     */
    public function canEdit(IdentityInterface $user, WikiPage $wikiPage): bool
    {
        return $this->authorization()->isResourceProjectMember($user, $wikiPage);
    }

    /**
     * Only project admins may delete wiki pages.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\WikiPage $wikiPage The page being deleted.
     * @return bool
     */
    public function canDelete(IdentityInterface $user, WikiPage $wikiPage): bool
    {
        return $this->authorization()->isResourceProjectAdmin($user, $wikiPage);
    }

    /**
     * @return \App\Authorization\ProjectAuthorization
     */
    private function authorization(): ProjectAuthorization
    {
        return $this->authorization ?? new ProjectAuthorization();
    }
}
