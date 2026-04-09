<?php
declare(strict_types=1);

namespace App\Policy;

use App\Authorization\RelatedProjectAuthorization;
use App\Model\Entity\IssueRelation;
use Authorization\IdentityInterface;

/**
 * Issue relation entity policy.
 */
class IssueRelationPolicy
{
    /**
     * @param \App\Authorization\RelatedProjectAuthorization|null $authorization Shared related-resource helper.
     */
    public function __construct(private readonly ?RelatedProjectAuthorization $authorization = null)
    {
    }

    /**
     * Project members may add issue relations in their projects.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\IssueRelation $issueRelation The relation being created.
     * @return bool
     */
    public function canAdd(IdentityInterface $user, IssueRelation $issueRelation): bool
    {
        return $this->authorization()->isIssueResourceProjectMember($user, $issueRelation);
    }

    /**
     * Project members may view issue relations in their projects.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\IssueRelation $issueRelation The relation being viewed.
     * @return bool
     */
    public function canView(IdentityInterface $user, IssueRelation $issueRelation): bool
    {
        return $this->authorization()->isIssueResourceProjectMember($user, $issueRelation);
    }

    /**
     * Project members may edit issue relations in their projects.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\IssueRelation $issueRelation The relation being edited.
     * @return bool
     */
    public function canEdit(IdentityInterface $user, IssueRelation $issueRelation): bool
    {
        return $this->authorization()->isIssueResourceProjectMember($user, $issueRelation);
    }

    /**
     * Project members may delete issue relations in their projects.
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\IssueRelation $issueRelation The relation being deleted.
     * @return bool
     */
    public function canDelete(IdentityInterface $user, IssueRelation $issueRelation): bool
    {
        return $this->authorization()->isIssueResourceProjectMember($user, $issueRelation);
    }

    /**
     * @return \App\Authorization\RelatedProjectAuthorization
     */
    private function authorization(): RelatedProjectAuthorization
    {
        return $this->authorization ?? new RelatedProjectAuthorization();
    }
}
