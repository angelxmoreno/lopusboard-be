<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AttachmentsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\AttachmentsTable Test Case
 */
class AttachmentsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\AttachmentsTable
     */
    protected $Attachments;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.ActivityLog',
        'app.Attachments',
        'app.Projects',
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
        $config = $this->getTableLocator()->exists('Attachments') ? [] : ['className' => AttachmentsTable::class];
        $this->Attachments = $this->getTableLocator()->get('Attachments', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Attachments);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\AttachmentsTable::validationDefault()
     */
    public function testInitializeAssociations(): void
    {
        $this->assertSame('Users', $this->Attachments->getAssociation('Uploaders')->getClassName());
        $this->assertSame('uploaded_by', $this->Attachments->getAssociation('Uploaders')->getForeignKey());
    }

    /**
     * @return void
     */
    public function testValidationDefaultRejectsInvalidProviderAndUrl(): void
    {
        $attachment = $this->Attachments->newEntity(
            [
                'project_id' => 1,
                'storage_provider' => 'dropbox',
                'filename' => 'bad.txt',
                'external_url' => 'not-a-url',
                'uploaded_by' => 1,
            ],
            ['accessibleFields' => ['*' => true]],
        );

        $this->assertArrayHasKey('storage_provider', $attachment->getErrors());
        $this->assertArrayHasKey('external_url', $attachment->getErrors());
    }

    /**
     * @return void
     */
    public function testSaveWritesAttachmentActivity(): void
    {
        $attachment = $this->Attachments->newEntity(
            [
                'project_id' => 1,
                'storage_provider' => 'google_drive',
                'filename' => 'logged.pdf',
                'uploaded_by' => 1,
            ],
            ['accessibleFields' => ['project_id' => true, 'uploaded_by' => true]],
        );

        $saved = $this->Attachments->save($attachment);

        $this->assertNotFalse($saved);
        $activityLog = $this->getTableLocator()->get('ActivityLog');
        $this->assertSame(1, $activityLog->find()->where([
            'subject_type' => 'attachment',
            'action' => 'file_attached',
            'subject_id' => $attachment->id,
        ])->count());
    }
}
