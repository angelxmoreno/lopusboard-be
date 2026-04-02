<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ProjectMembersFixture
 */
class ProjectMembersFixture extends TestFixture
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
                'user_id' => 1,
                'role' => 'admin',
                'created' => '2026-03-31 05:06:16',
                'modified' => '2026-03-31 05:06:16',
            ],
            [
                'id' => 2,
                'project_id' => 1,
                'user_id' => 2,
                'role' => 'member',
                'created' => '2026-03-31 05:06:16',
                'modified' => '2026-03-31 05:06:16',
            ],
        ];
        parent::init();
    }
}
