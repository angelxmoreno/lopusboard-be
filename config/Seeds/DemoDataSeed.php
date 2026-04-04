<?php
declare(strict_types=1);

use App\Model\Enum\AttachmentLinkableType;
use App\Model\Enum\AttachmentStorageProvider;
use App\Model\Enum\IssuePriority;
use App\Model\Enum\IssueType;
use App\Model\Enum\ProjectMemberRole;
use App\Model\Table\AttachmentLinksTable;
use App\Model\Table\AttachmentsTable;
use App\Model\Table\CommentsTable;
use App\Model\Table\DepartmentsTable;
use App\Model\Table\IssueRelationsTable;
use App\Model\Table\IssuesTable;
use App\Model\Table\ProjectMembersTable;
use App\Model\Table\ProjectsTable;
use App\Model\Table\StatusesTable;
use App\Model\Table\UsersTable;
use App\Model\Table\WikiPagesTable;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Utility\Text;
use Migrations\BaseSeed;

/**
 * Seeds a rerunnable demo user plus placeholder project data for local development.
 */
class DemoDataSeed extends BaseSeed
{
    private const DEMO_USER_EMAIL = 'demo@lopusboard.test';
    private const DEMO_USER_APPWRITE_ID = 'seed-demo-user';
    private const DEMO_PROJECT_SLUG = 'demo-workspace';
    private const DEMO_ATTACHMENT_EXTERNAL_ID = 'seed-demo-roadmap';

    /**
     * Writes the demo dataset.
     *
     * @return void
     * @throws \Exception
     */
    public function run(): void
    {
        $seedConnectionName = $this->getAdapter()->getConnection()->configName();
        $existingDefaultAlias = ConnectionManager::aliases()['default'] ?? null;
        if ($seedConnectionName !== 'default') {
            ConnectionManager::alias($seedConnectionName, 'default');
        }

        try {
            $locator = FactoryLocator::get('Table');

            /** @var \App\Model\Table\UsersTable $usersTable */
            $usersTable = $locator->get('Users');
            /** @var \App\Model\Table\ProjectsTable $projectsTable */
            $projectsTable = $locator->get('Projects');
            /** @var \App\Model\Table\ProjectMembersTable $projectMembersTable */
            $projectMembersTable = $locator->get('ProjectMembers');
            /** @var \App\Model\Table\StatusesTable $statusesTable */
            $statusesTable = $locator->get('Statuses');
            /** @var \App\Model\Table\DepartmentsTable $departmentsTable */
            $departmentsTable = $locator->get('Departments');
            /** @var \App\Model\Table\IssuesTable $issuesTable */
            $issuesTable = $locator->get('Issues');
            /** @var \App\Model\Table\CommentsTable $commentsTable */
            $commentsTable = $locator->get('Comments');
            /** @var \App\Model\Table\IssueRelationsTable $issueRelationsTable */
            $issueRelationsTable = $locator->get('IssueRelations');
            /** @var \App\Model\Table\WikiPagesTable $wikiPagesTable */
            $wikiPagesTable = $locator->get('WikiPages');
            /** @var \App\Model\Table\AttachmentsTable $attachmentsTable */
            $attachmentsTable = $locator->get('Attachments');
            /** @var \App\Model\Table\AttachmentLinksTable $attachmentLinksTable */
            $attachmentLinksTable = $locator->get('AttachmentLinks');

            $usersTable->getConnection()->transactional(function () use (
                $attachmentsTable,
                $attachmentLinksTable,
                $commentsTable,
                $departmentsTable,
                $issueRelationsTable,
                $issuesTable,
                $projectMembersTable,
                $projectsTable,
                $statusesTable,
                $usersTable,
                $wikiPagesTable,
            ): void {
                $user = $this->ensureUser($usersTable);
                $project = $this->ensureProject($projectsTable, (int)$user->get('id'));
                $projectId = (int)$project->get('id');
                $userId = (int)$user->get('id');

                $this->ensureProjectMembership($projectMembersTable, $projectId, $userId);

                $todoStatusId = $this->findStatusId($statusesTable, $projectId, 'Todo');
                $doneStatusId = $this->findStatusId($statusesTable, $projectId, 'Done');
                $engineeringDepartmentId = $this->findDepartmentId($departmentsTable, $projectId, 'Engineering');
                $designDepartmentId = $this->findDepartmentId($departmentsTable, $projectId, 'Design');

                $roadmapIssue = $this->ensureIssue(
                    $issuesTable,
                    [
                        'project_id' => $projectId,
                        'type' => IssueType::Issue->value,
                        'title' => 'Launch the first demo workspace',
                        'description' => 'Set up a polished workspace that shows issues, wiki content, and attached files.',
                        'status_id' => $todoStatusId,
                        'priority' => IssuePriority::High->value,
                        'assignee_id' => $userId,
                        'department_id' => $engineeringDepartmentId,
                        'due_date' => '2026-04-17',
                        'created_by' => $userId,
                    ],
                );

                $authIssue = $this->ensureIssue(
                    $issuesTable,
                    [
                        'project_id' => $projectId,
                        'type' => IssueType::Issue->value,
                        'title' => 'Connect Appwrite auth to the API',
                        'description' => 'Verify JWTs on API requests and sync the local user record on first sign-in.',
                        'status_id' => $doneStatusId,
                        'priority' => IssuePriority::Critical->value,
                        'assignee_id' => $userId,
                        'department_id' => $engineeringDepartmentId,
                        'due_date' => '2026-04-10',
                        'created_by' => $userId,
                    ],
                );

                $this->ensureIssue(
                    $issuesTable,
                    [
                        'parent_id' => (int)$roadmapIssue->get('id'),
                        'title' => 'Create the first kanban-ready seed issue',
                        'description' => 'Use a child task so frontend development has predictable sample data.',
                        'status_id' => $todoStatusId,
                        'priority' => IssuePriority::Medium->value,
                        'assignee_id' => $userId,
                        'department_id' => $designDepartmentId,
                        'due_date' => '2026-04-14',
                        'created_by' => $userId,
                    ],
                );

                $this->ensureComment(
                    $commentsTable,
                    (int)$roadmapIssue->get('id'),
                    $userId,
                    'This workspace is seeded for local API work. Feel free to edit or delete these records.',
                );

                $this->ensureIssueRelation(
                    $issueRelationsTable,
                    (int)$roadmapIssue->get('id'),
                    (int)$authIssue->get('id'),
                    'depends_on',
                );

                $this->ensureWikiPage(
                    $wikiPagesTable,
                    [
                        'project_id' => $projectId,
                        'title' => 'Team Rituals',
                        'body' => "## Weekly cadence\n\n- Monday planning\n- Wednesday design review\n- Friday release check",
                        'position' => 1,
                        'created_by' => $userId,
                        'last_edited_by' => $userId,
                    ],
                );

                $gettingStartedPage = $this->ensureWikiPage(
                    $wikiPagesTable,
                    [
                        'project_id' => $projectId,
                        'title' => 'Getting Started',
                        'body' => "Welcome to the demo workspace.\n\nStart with [[Team Rituals]] and then open the launch issue board.",
                        'position' => 2,
                        'created_by' => $userId,
                        'last_edited_by' => $userId,
                    ],
                );

                $attachment = $this->ensureAttachment(
                    $attachmentsTable,
                    [
                        'project_id' => $projectId,
                        'storage_provider' => AttachmentStorageProvider::GoogleDrive->value,
                        'filename' => 'demo-roadmap.pdf',
                        'mime_type' => 'application/pdf',
                        'file_size' => 245760,
                        'storage_path' => 'demo/demo-roadmap.pdf',
                        'external_url' => 'https://example.com/demo-roadmap.pdf',
                        'external_id' => self::DEMO_ATTACHMENT_EXTERNAL_ID,
                        'uploaded_by' => $userId,
                    ],
                );

                $this->ensureAttachmentLink(
                    $attachmentLinksTable,
                    (int)$attachment->get('id'),
                    AttachmentLinkableType::Issue->value,
                    (int)$roadmapIssue->get('id'),
                );

                $this->ensureAttachmentLink(
                    $attachmentLinksTable,
                    (int)$attachment->get('id'),
                    AttachmentLinkableType::WikiPage->value,
                    (int)$gettingStartedPage->get('id'),
                );
            });
        } finally {
            FactoryLocator::get('Table')->clear();

            if ($seedConnectionName !== 'default') {
                if ($existingDefaultAlias !== null) {
                    ConnectionManager::alias($existingDefaultAlias, 'default');
                } else {
                    ConnectionManager::dropAlias('default');
                }
            }
        }
    }

    /**
     * Creates or reuses the demo user.
     *
     * @param \App\Model\Table\UsersTable $usersTable The users table.
     * @return \Cake\Datasource\EntityInterface
     */
    private function ensureUser(UsersTable $usersTable): EntityInterface
    {
        $existingUser = $usersTable->find()
            ->where(['email' => self::DEMO_USER_EMAIL])
            ->first();
        if ($existingUser !== null) {
            return $existingUser;
        }

        $user = $usersTable->newEntity(
            [
                'appwrite_id' => self::DEMO_USER_APPWRITE_ID,
                'name' => 'Demo User',
                'email' => self::DEMO_USER_EMAIL,
                'avatar_url' => 'https://example.com/avatar/demo-user.png',
            ],
            ['accessibleFields' => ['appwrite_id' => true, 'name' => true, 'email' => true, 'avatar_url' => true]],
        );

        return $usersTable->saveOrFail($user);
    }

    /**
     * Creates or reuses the demo project.
     *
     * @param \App\Model\Table\ProjectsTable $projectsTable The projects table.
     * @param int $userId The owner user identifier.
     * @return \Cake\Datasource\EntityInterface
     */
    private function ensureProject(ProjectsTable $projectsTable, int $userId): EntityInterface
    {
        $existingProject = $projectsTable->find()
            ->where(['slug' => self::DEMO_PROJECT_SLUG])
            ->first();
        if ($existingProject !== null) {
            return $existingProject;
        }

        $project = $projectsTable->newEntity(
            [
                'name' => 'Demo Workspace',
                'slug' => self::DEMO_PROJECT_SLUG,
                'description' => 'Local placeholder workspace seeded for API and frontend development.',
                'created_by' => $userId,
            ],
            ['accessibleFields' => ['name' => true, 'slug' => true, 'description' => true, 'created_by' => true]],
        );

        return $projectsTable->saveOrFail($project);
    }

    /**
     * Ensures the demo user is a project admin.
     *
     * @param \App\Model\Table\ProjectMembersTable $projectMembersTable The members table.
     * @param int $projectId The project identifier.
     * @param int $userId The user identifier.
     * @return void
     */
    private function ensureProjectMembership(
        ProjectMembersTable $projectMembersTable,
        int $projectId,
        int $userId,
    ): void {
        $existingMembership = $projectMembersTable->find()
            ->where(['project_id' => $projectId, 'user_id' => $userId])
            ->first();
        if ($existingMembership !== null) {
            return;
        }

        $membership = $projectMembersTable->newEntity(
            [
                'project_id' => $projectId,
                'user_id' => $userId,
                'role' => ProjectMemberRole::Admin->value,
            ],
            ['accessibleFields' => ['project_id' => true, 'user_id' => true, 'role' => true]],
        );

        $projectMembersTable->saveOrFail($membership);
    }

    /**
     * Finds a seeded status identifier by name.
     *
     * @param \App\Model\Table\StatusesTable $statusesTable The statuses table.
     * @param int $projectId The project identifier.
     * @param string $name The status name.
     * @return int
     */
    private function findStatusId(
        StatusesTable $statusesTable,
        int $projectId,
        string $name,
    ): int {
        $status = $statusesTable->find()
            ->select(['id'])
            ->where(['project_id' => $projectId, 'name' => $name])
            ->disableHydration()
            ->firstOrFail();

        return (int)$status['id'];
    }

    /**
     * Finds a seeded department identifier by name.
     *
     * @param \App\Model\Table\DepartmentsTable $departmentsTable The departments table.
     * @param int $projectId The project identifier.
     * @param string $name The department name.
     * @return int
     */
    private function findDepartmentId(
        DepartmentsTable $departmentsTable,
        int $projectId,
        string $name,
    ): int {
        $department = $departmentsTable->find()
            ->select(['id'])
            ->where(['project_id' => $projectId, 'name' => $name])
            ->disableHydration()
            ->firstOrFail();

        return (int)$department['id'];
    }

    /**
     * Creates or reuses a seeded issue.
     *
     * @param \App\Model\Table\IssuesTable $issuesTable The issues table.
     * @param array<string, mixed> $data The issue payload.
     * @return \Cake\Datasource\EntityInterface
     */
    private function ensureIssue(IssuesTable $issuesTable, array $data): EntityInterface
    {
        $conditions = [
            'title' => $data['title'],
            'created_by' => $data['created_by'],
        ];
        if (isset($data['parent_id'])) {
            $conditions['parent_id'] = $data['parent_id'];
        } else {
            $conditions['project_id'] = $data['project_id'];
        }

        $existingIssue = $issuesTable->find()
            ->where($conditions)
            ->first();
        if ($existingIssue !== null) {
            return $existingIssue;
        }

        $issue = $issuesTable->newEntity($data, [
            'accessibleFields' => [
                'project_id' => true,
                'parent_id' => true,
                'type' => true,
                'title' => true,
                'description' => true,
                'status_id' => true,
                'priority' => true,
                'assignee_id' => true,
                'department_id' => true,
                'due_date' => true,
                'position' => true,
                'created_by' => true,
            ],
        ]);

        return $issuesTable->saveOrFail($issue);
    }

    /**
     * Creates or reuses a seeded comment.
     *
     * @param \App\Model\Table\CommentsTable $commentsTable The comments table.
     * @param int $issueId The issue identifier.
     * @param int $userId The author identifier.
     * @param string $body The comment body.
     * @return void
     */
    private function ensureComment(
        CommentsTable $commentsTable,
        int $issueId,
        int $userId,
        string $body,
    ): void {
        $existingComment = $commentsTable->find()
            ->where(['issue_id' => $issueId, 'user_id' => $userId, 'body' => $body])
            ->first();
        if ($existingComment !== null) {
            return;
        }

        $comment = $commentsTable->newEntity(
            [
                'issue_id' => $issueId,
                'user_id' => $userId,
                'body' => $body,
            ],
            ['accessibleFields' => ['issue_id' => true, 'user_id' => true, 'body' => true]],
        );

        $commentsTable->saveOrFail($comment);
    }

    /**
     * Creates or reuses a seeded issue relation.
     *
     * @param \App\Model\Table\IssueRelationsTable $issueRelationsTable The relations table.
     * @param int $issueId The source issue identifier.
     * @param int $relatedIssueId The related issue identifier.
     * @param string $type The relation type.
     * @return void
     */
    private function ensureIssueRelation(
        IssueRelationsTable $issueRelationsTable,
        int $issueId,
        int $relatedIssueId,
        string $type,
    ): void {
        $existingRelation = $issueRelationsTable->find()
            ->where([
                'issue_id' => $issueId,
                'related_issue_id' => $relatedIssueId,
                'type' => $type,
            ])
            ->first();
        if ($existingRelation !== null) {
            return;
        }

        $relation = $issueRelationsTable->newEntity(
            [
                'issue_id' => $issueId,
                'related_issue_id' => $relatedIssueId,
                'type' => $type,
            ],
            ['accessibleFields' => ['issue_id' => true, 'related_issue_id' => true, 'type' => true]],
        );

        $issueRelationsTable->saveOrFail($relation);
    }

    /**
     * Creates or reuses a seeded wiki page.
     *
     * @param \App\Model\Table\WikiPagesTable $wikiPagesTable The wiki pages table.
     * @param array<string, mixed> $data The page payload.
     * @return \Cake\Datasource\EntityInterface
     */
    private function ensureWikiPage(WikiPagesTable $wikiPagesTable, array $data): EntityInterface
    {
        $slug = strtolower(Text::slug((string)$data['title'], ['replacement' => '-']));
        $existingPage = $wikiPagesTable->find()
            ->where(['project_id' => $data['project_id'], 'slug' => $slug])
            ->first();
        if ($existingPage !== null) {
            return $existingPage;
        }

        $page = $wikiPagesTable->newEntity($data, [
            'accessibleFields' => [
                'project_id' => true,
                'parent_id' => true,
                'title' => true,
                'slug' => true,
                'body' => true,
                'position' => true,
                'created_by' => true,
                'last_edited_by' => true,
            ],
        ]);

        return $wikiPagesTable->saveOrFail($page);
    }

    /**
     * Creates or reuses a seeded attachment.
     *
     * @param \App\Model\Table\AttachmentsTable $attachmentsTable The attachments table.
     * @param array<string, mixed> $data The attachment payload.
     * @return \Cake\Datasource\EntityInterface
     */
    private function ensureAttachment(AttachmentsTable $attachmentsTable, array $data): EntityInterface
    {
        $existingAttachment = $attachmentsTable->find()
            ->where([
                'project_id' => $data['project_id'],
                'external_id' => $data['external_id'],
            ])
            ->first();
        if ($existingAttachment !== null) {
            return $existingAttachment;
        }

        $attachment = $attachmentsTable->newEntity($data, [
            'accessibleFields' => [
                'project_id' => true,
                'storage_provider' => true,
                'filename' => true,
                'mime_type' => true,
                'file_size' => true,
                'storage_path' => true,
                'external_url' => true,
                'external_id' => true,
                'uploaded_by' => true,
            ],
        ]);

        return $attachmentsTable->saveOrFail($attachment);
    }

    /**
     * Creates or reuses an attachment link.
     *
     * @param \App\Model\Table\AttachmentLinksTable $attachmentLinksTable The attachment links table.
     * @param int $attachmentId The attachment identifier.
     * @param string $linkableType The linked record type.
     * @param int $linkableId The linked record identifier.
     * @return void
     */
    private function ensureAttachmentLink(
        AttachmentLinksTable $attachmentLinksTable,
        int $attachmentId,
        string $linkableType,
        int $linkableId,
    ): void {
        $existingLink = $attachmentLinksTable->find()
            ->where([
                'attachment_id' => $attachmentId,
                'linkable_type' => $linkableType,
                'linkable_id' => $linkableId,
            ])
            ->first();
        if ($existingLink !== null) {
            return;
        }

        $attachmentLink = $attachmentLinksTable->newEntity(
            [
                'attachment_id' => $attachmentId,
                'linkable_type' => $linkableType,
                'linkable_id' => $linkableId,
            ],
            ['accessibleFields' => ['attachment_id' => true, 'linkable_type' => true, 'linkable_id' => true]],
        );

        $attachmentLinksTable->saveOrFail($attachmentLink);
    }
}
