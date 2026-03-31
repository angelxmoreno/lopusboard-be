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
                'storage_provider' => 'Lorem ipsum dolor sit amet',
                'filename' => 'Lorem ipsum dolor sit amet',
                'mime_type' => 'Lorem ipsum dolor sit amet',
                'file_size' => 1,
                'storage_path' => 'Lorem ipsum dolor sit amet',
                'external_url' => 'Lorem ipsum dolor sit amet',
                'external_id' => 'Lorem ipsum dolor sit amet',
                'uploaded_by' => 1,
                'created' => '2026-03-31 05:06:15',
                'modified' => '2026-03-31 05:06:15',
            ],
        ];
        parent::init();
    }
}
