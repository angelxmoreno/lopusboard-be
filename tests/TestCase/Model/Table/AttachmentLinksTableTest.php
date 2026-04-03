<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AttachmentLinksTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\AttachmentLinksTable Test Case
 */
class AttachmentLinksTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\AttachmentLinksTable
     */
    protected $AttachmentLinks;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.ActivityLog',
        'app.AttachmentLinks',
        'app.Attachments',
        'app.Issues',
        'app.WikiPages',
        'app.Projects',
        'app.Statuses',
        'app.Users',
        'app.Departments',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('AttachmentLinks') ? [] : ['className' => AttachmentLinksTable::class];
        $this->AttachmentLinks = $this->getTableLocator()->get('AttachmentLinks', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->AttachmentLinks);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\AttachmentLinksTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $attachmentLink = $this->AttachmentLinks->newEntity(
            [
                'attachment_id' => 1,
                'linkable_type' => 'comment',
                'linkable_id' => 1,
            ],
            ['accessibleFields' => ['*' => true]],
        );

        $this->assertArrayHasKey('linkable_type', $attachmentLink->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\AttachmentLinksTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $attachmentLink = $this->AttachmentLinks->newEntity(
            [
                'attachment_id' => 1,
                'linkable_type' => 'wiki_page',
                'linkable_id' => 999,
            ],
            ['accessibleFields' => ['*' => true]],
        );

        $this->assertFalse($this->AttachmentLinks->save($attachmentLink));
        $this->assertArrayHasKey('linkable_id', $attachmentLink->getErrors());
    }

    /**
     * @return void
     */
    public function testSaveWritesAttachmentLinkActivity(): void
    {
        $attachmentLink = $this->AttachmentLinks->newEntity(
            [
                'attachment_id' => 1,
                'linkable_type' => 'wiki_page',
                'linkable_id' => 1,
            ],
            ['accessibleFields' => ['*' => true]],
        );

        $saved = $this->AttachmentLinks->save($attachmentLink);

        $this->assertNotFalse($saved);
        $activityLog = $this->getTableLocator()->get('ActivityLog');
        $this->assertSame(1, $activityLog->find()->where([
            'subject_type' => 'wiki_page',
            'subject_id' => 1,
            'action' => 'file_attached',
        ])->count());
    }
}
