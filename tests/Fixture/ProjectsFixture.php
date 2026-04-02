<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ProjectsFixture
 */
class ProjectsFixture extends TestFixture
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
                'name' => 'Project One',
                'slug' => 'project-one',
                'description' => 'Primary project fixture.',
                'created_by' => 1,
                'created' => '2026-03-31 05:06:16',
                'modified' => '2026-03-31 05:06:16',
            ],
            [
                'id' => 2,
                'name' => 'Project Two',
                'slug' => 'project-two',
                'description' => 'Secondary project fixture.',
                'created_by' => 2,
                'created' => '2026-03-31 05:06:16',
                'modified' => '2026-03-31 05:06:16',
            ],
        ];
        parent::init();
    }
}
