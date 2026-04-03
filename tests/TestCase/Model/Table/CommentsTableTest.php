<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CommentsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CommentsTable Test Case
 */
class CommentsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\CommentsTable
     */
    protected $Comments;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.ActivityLog',
        'app.Comments',
        'app.Departments',
        'app.Issues',
        'app.Projects',
        'app.Statuses',
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Comments') ? [] : ['className' => CommentsTable::class];
        $this->Comments = $this->getTableLocator()->get('Comments', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Comments);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\CommentsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $comment = $this->Comments->newEntity([
            'issue_id' => 1,
            'user_id' => 1,
            'body' => '',
        ], ['accessibleFields' => ['issue_id' => true, 'user_id' => true]]);

        $this->assertArrayHasKey('body', $comment->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\CommentsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $comment = $this->Comments->newEntity([
            'issue_id' => 999,
            'user_id' => 999,
            'body' => 'Invalid references.',
        ], ['accessibleFields' => ['issue_id' => true, 'user_id' => true]]);

        $this->assertFalse($this->Comments->save($comment));
        $this->assertArrayHasKey('issue_id', $comment->getErrors());
        $this->assertArrayHasKey('user_id', $comment->getErrors());
    }

    /**
     * @return void
     */
    public function testSaveWritesCommentActivity(): void
    {
        $comment = $this->Comments->newEntity([
            'issue_id' => 1,
            'user_id' => 1,
            'body' => 'Logged comment.',
        ], ['accessibleFields' => ['issue_id' => true, 'user_id' => true]]);

        $saved = $this->Comments->save($comment);

        $this->assertNotFalse($saved);
        $activityLog = $this->getTableLocator()->get('ActivityLog');
        $this->assertSame(1, $activityLog->find()->where([
            'subject_type' => 'comment',
            'action' => 'comment_added',
            'subject_id' => $comment->id,
        ])->count());
    }
}
