<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * DepartmentsFixture
 */
class DepartmentsFixture extends TestFixture
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
                'name' => 'Engineering',
                'color' => '#2563EB',
                'position' => 1.5,
                'created' => '2026-03-31 05:06:15',
                'modified' => '2026-03-31 05:06:15',
            ],
            [
                'id' => 2,
                'project_id' => 2,
                'name' => 'Design',
                'color' => '#9333EA',
                'position' => 2.5,
                'created' => '2026-03-31 05:06:15',
                'modified' => '2026-03-31 05:06:15',
            ],
        ];
        parent::init();
    }
}
