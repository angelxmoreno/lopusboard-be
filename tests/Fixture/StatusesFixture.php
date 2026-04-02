<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * StatusesFixture
 */
class StatusesFixture extends TestFixture
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
                'name' => 'Todo',
                'color' => '#6B7280',
                'position' => 1.5,
                'is_default' => 1,
                'is_done' => 0,
                'created' => '2026-03-31 05:06:16',
                'modified' => '2026-03-31 05:06:16',
            ],
            [
                'id' => 2,
                'project_id' => 2,
                'name' => 'Done',
                'color' => '#10B981',
                'position' => 2.5,
                'is_default' => 0,
                'is_done' => 1,
                'created' => '2026-03-31 05:06:16',
                'modified' => '2026-03-31 05:06:16',
            ],
        ];
        parent::init();
    }
}
