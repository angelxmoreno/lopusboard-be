<?php
declare(strict_types=1);

namespace App\Authorization;

use App\Model\Table\IssuesTable;
use App\Model\Table\WikiPagesTable;
use Authorization\IdentityInterface;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * Shared authorization helpers for resources that resolve project access
 * through related issues or wiki pages.
 */
class RelatedProjectAuthorization
{
    use LocatorAwareTrait;

    /**
     * @param \App\Authorization\ProjectAuthorization|null $projects
     */
    public function __construct(private readonly ?ProjectAuthorization $projects = null)
    {
    }

    /**
     * @param \Authorization\IdentityInterface $user
     * @param object $resource
     * @param string $issueField
     * @return bool
     */
    public function isIssueResourceProjectMember(
        IdentityInterface $user,
        object $resource,
        string $issueField = 'issue_id',
    ): bool {
        $issueId = $this->resourceId($resource, $issueField);
        if ($issueId === null) {
            return $this->projects()->isAuthenticated($user);
        }

        return $this->isIssueProjectMember($user, $issueId);
    }

    /**
     * @param \Authorization\IdentityInterface $user
     * @param object $resource
     * @param string $issueField
     * @return bool
     */
    public function isIssueResourceProjectAdmin(
        IdentityInterface $user,
        object $resource,
        string $issueField = 'issue_id',
    ): bool {
        $issueId = $this->resourceId($resource, $issueField);
        if ($issueId === null) {
            return $this->projects()->isAuthenticated($user);
        }

        return $this->isIssueProjectAdmin($user, $issueId);
    }

    /**
     * @param \Authorization\IdentityInterface $user
     * @param object $resource
     * @param string $wikiPageField
     * @return bool
     */
    public function isWikiPageResourceProjectMember(
        IdentityInterface $user,
        object $resource,
        string $wikiPageField = 'wiki_page_id',
    ): bool {
        $wikiPageId = $this->resourceId($resource, $wikiPageField);
        if ($wikiPageId === null) {
            return $this->projects()->isAuthenticated($user);
        }

        return $this->isWikiPageProjectMember($user, $wikiPageId);
    }

    /**
     * @param \Authorization\IdentityInterface $user
     * @param object $resource
     * @param string $wikiPageField
     * @return bool
     */
    public function isWikiPageResourceProjectAdmin(
        IdentityInterface $user,
        object $resource,
        string $wikiPageField = 'wiki_page_id',
    ): bool {
        $wikiPageId = $this->resourceId($resource, $wikiPageField);
        if ($wikiPageId === null) {
            return $this->projects()->isAuthenticated($user);
        }

        return $this->isWikiPageProjectAdmin($user, $wikiPageId);
    }

    /**
     * @param \Authorization\IdentityInterface $user
     * @param int $issueId
     * @return bool
     */
    public function isIssueProjectMember(IdentityInterface $user, int $issueId): bool
    {
        $projectId = $this->issueProjectId($issueId);
        if ($projectId === null) {
            return false;
        }

        return $this->projects()->isProjectMember($user, $projectId);
    }

    /**
     * @param \Authorization\IdentityInterface $user
     * @param int $issueId
     * @return bool
     */
    public function isIssueProjectAdmin(IdentityInterface $user, int $issueId): bool
    {
        $projectId = $this->issueProjectId($issueId);
        if ($projectId === null) {
            return false;
        }

        return $this->projects()->isProjectAdmin($user, $projectId);
    }

    /**
     * @param \Authorization\IdentityInterface $user
     * @param int $wikiPageId
     * @return bool
     */
    public function isWikiPageProjectMember(IdentityInterface $user, int $wikiPageId): bool
    {
        $projectId = $this->wikiPageProjectId($wikiPageId);
        if ($projectId === null) {
            return false;
        }

        return $this->projects()->isProjectMember($user, $projectId);
    }

    /**
     * @param \Authorization\IdentityInterface $user
     * @param int $wikiPageId
     * @return bool
     */
    public function isWikiPageProjectAdmin(IdentityInterface $user, int $wikiPageId): bool
    {
        $projectId = $this->wikiPageProjectId($wikiPageId);
        if ($projectId === null) {
            return false;
        }

        return $this->projects()->isProjectAdmin($user, $projectId);
    }

    /**
     * @param object $resource
     * @param string $field
     * @return int|null
     */
    private function resourceId(object $resource, string $field): ?int
    {
        $resourceId = $resource->{$field} ?? null;
        if (is_int($resourceId)) {
            return $resourceId;
        }
        if (is_string($resourceId) && ctype_digit($resourceId)) {
            return (int)$resourceId;
        }

        return null;
    }

    /**
     * @param int $issueId
     * @return int|null
     */
    private function issueProjectId(int $issueId): ?int
    {
        $issue = $this->issues()
            ->find()
            ->select(['project_id'])
            ->where(['Issues.id' => $issueId])
            ->disableHydration()
            ->first();

        return $issue === null ? null : (int)$issue['project_id'];
    }

    /**
     * @param int $wikiPageId
     * @return int|null
     */
    private function wikiPageProjectId(int $wikiPageId): ?int
    {
        $wikiPage = $this->wikiPages()
            ->find()
            ->select(['project_id'])
            ->where(['WikiPages.id' => $wikiPageId])
            ->disableHydration()
            ->first();

        return $wikiPage === null ? null : (int)$wikiPage['project_id'];
    }

    /**
     * @return \App\Authorization\ProjectAuthorization
     */
    private function projects(): ProjectAuthorization
    {
        return $this->projects ?? new ProjectAuthorization();
    }

    /**
     * @return \App\Model\Table\IssuesTable
     */
    private function issues(): IssuesTable
    {
        /** @var \App\Model\Table\IssuesTable */
        return $this->getTableLocator()->get(IssuesTable::class);
    }

    /**
     * @return \App\Model\Table\WikiPagesTable
     */
    private function wikiPages(): WikiPagesTable
    {
        /** @var \App\Model\Table\WikiPagesTable */
        return $this->getTableLocator()->get(WikiPagesTable::class);
    }
}
