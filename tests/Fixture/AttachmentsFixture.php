<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AttachmentsFixture
 */
class AttachmentsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'project_id' => 1,
                'storage_provider' => 'google_drive',
                'filename' => 'attachment.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 1,
                'storage_path' => 'drive://attachments/1',
                'external_url' => 'https://drive.google.com/file/d/1/view',
                'external_id' => 'drive-file-1',
                'uploaded_by' => 1,
                'created' => '2026-03-31 05:06:15',
                'modified' => '2026-03-31 05:06:15',
            ],
        ];
        parent::init();
    }
}
