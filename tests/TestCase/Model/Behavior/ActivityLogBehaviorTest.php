<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Behavior;

use App\Model\Table\AttachmentsTable;
use App\Model\Table\CommentsTable;
use App\Model\Table\IssuesTable;
use App\Model\Table\ProjectMembersTable;
use App\Model\Table\WikiPagesTable;
use Cake\TestSuite\TestCase;

/**
 * ActivityLogBehavior test case.
 */
class ActivityLogBehaviorTest extends TestCase
{
    /**
     * @var \App\Model\Table\IssuesTable
     */
    protected IssuesTable $Issues;

    /**
     * @var \App\Model\Table\CommentsTable
     */
    protected CommentsTable $Comments;

    /**
     * @var \App\Model\Table\AttachmentsTable
     */
    protected AttachmentsTable $Attachments;

    /**
     * @var \App\Model\Table\WikiPagesTable
     */
    protected WikiPagesTable $WikiPages;

    /**
     * @var \App\Model\Table\ProjectMembersTable
     */
    protected ProjectMembersTable $ProjectMembers;

    /**
     * @var array<string>
     */
    protected array $fixtures = [
        'app.ActivityLog',
        'app.Attachments',
        'app.Comments',
        'app.Departments',
        'app.Issues',
        'app.ProjectMembers',
        'app.Projects',
        'app.Statuses',
        'app.Users',
        'app.WikiPages',
        'app.WikiPageLinks',
        'app.WikiPageRevisions',
    ];

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $locator = $this->getTableLocator();
        $this->Issues = $locator->get('Issues');
        $this->Comments = $locator->get('Comments');
        $this->Attachments = $locator->get('Attachments');
        $this->WikiPages = $locator->get('WikiPages');
        $this->ProjectMembers = $locator->get('ProjectMembers');
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Issues, $this->Comments, $this->Attachments, $this->WikiPages, $this->ProjectMembers);

        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testBehaviorAttachedToConfiguredTables(): void
    {
        $this->assertTrue($this->Issues->behaviors()->has('ActivityLog'));
        $this->assertTrue($this->Comments->behaviors()->has('ActivityLog'));
        $this->assertTrue($this->Attachments->behaviors()->has('ActivityLog'));
        $this->assertTrue($this->WikiPages->behaviors()->has('ActivityLog'));
        $this->assertTrue($this->ProjectMembers->behaviors()->has('ActivityLog'));
    }

    /**
     * @return void
     */
    public function testCommentAndAttachmentSavesCreateActivityRows(): void
    {
        $comment = $this->Comments->newEntity([
            'issue_id' => 1,
            'user_id' => 1,
            'body' => 'Behavior test comment.',
        ], ['accessibleFields' => ['issue_id' => true, 'user_id' => true]]);
        $this->assertNotFalse($this->Comments->save($comment));

        $attachment = $this->Attachments->newEntity([
            'project_id' => 1,
            'storage_provider' => 'google_drive',
            'filename' => 'behavior-test.pdf',
            'uploaded_by' => 1,
        ], ['accessibleFields' => ['project_id' => true, 'uploaded_by' => true]]);
        $this->assertNotFalse($this->Attachments->save($attachment));

        $activity = $this->Issues->getTableLocator()->get('ActivityLog');
        $this->assertSame(1, $activity->find()->where([
            'subject_type' => 'comment',
            'action' => 'comment_added',
            'subject_id' => $comment->id,
        ])->count());
        $this->assertSame(1, $activity->find()->where([
            'subject_type' => 'attachment',
            'action' => 'file_attached',
            'subject_id' => $attachment->id,
        ])->count());
    }
}
