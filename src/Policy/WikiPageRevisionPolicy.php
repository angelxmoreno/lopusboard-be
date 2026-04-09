<?php
declare(strict_types=1);

namespace App\Policy;

use App\Authorization\RelatedProjectAuthorization;
use App\Model\Entity\WikiPageRevision;
use Authorization\IdentityInterface;

/**
 * Wiki page revision entity policy.
 */
class WikiPageRevisionPolicy
{
    /**
     * @param \App\Authorization\RelatedProjectAuthorization|null $authorization Shared related-resource helper.
     */
    public function __construct(private readonly ?RelatedProjectAuthorization $authorization = null)
    {
    }

    /**
     * Project members may view wiki page revisions in their projects.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\WikiPageRevision $wikiPageRevision The revision being viewed.
     * @return bool
     */
    public function canView(IdentityInterface $user, WikiPageRevision $wikiPageRevision): bool
    {
        return $this->authorization()->isWikiPageResourceProjectMember($user, $wikiPageRevision);
    }

    /**
     * @return \App\Authorization\RelatedProjectAuthorization
     */
    private function authorization(): RelatedProjectAuthorization
    {
        return $this->authorization ?? new RelatedProjectAuthorization();
    }
}
