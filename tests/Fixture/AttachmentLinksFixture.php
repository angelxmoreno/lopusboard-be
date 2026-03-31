<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AttachmentLinksFixture
 */
class AttachmentLinksFixture extends TestFixture
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
                'attachment_id' => 1,
                'linkable_type' => 'issue',
                'linkable_id' => 1,
                'created' => '2026-03-31 05:06:15',
                'modified' => '2026-03-31 05:06:15',
            ],
        ];
        parent::init();
    }
}
