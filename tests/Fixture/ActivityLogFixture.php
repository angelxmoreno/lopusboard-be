<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ActivityLogFixture
 */
class ActivityLogFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'activity_log';
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
                'actor_id' => 1,
                'subject_type' => 'Lorem ipsum dolor sit amet',
                'subject_id' => 1,
                'action' => 'Lorem ipsum dolor sit amet',
                'old_value' => '',
                'new_value' => '',
                'created' => '2026-03-31 05:06:15',
                'modified' => '2026-03-31 05:06:15',
            ],
        ];
        parent::init();
    }
}
