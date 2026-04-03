<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * WikiPagesFixture
 */
class WikiPagesFixture extends TestFixture
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
                'parent_id' => null,
                'title' => 'Getting Started',
                'slug' => 'getting-started',
                'body' => 'Primary wiki page fixture.',
                'position' => 1.5,
                'created_by' => 1,
                'last_edited_by' => 1,
                'created' => '2026-03-31 05:06:16',
                'modified' => '2026-03-31 05:06:16',
            ],
            [
                'id' => 2,
                'project_id' => 2,
                'parent_id' => null,
                'title' => 'Release Notes',
                'slug' => 'release-notes',
                'body' => 'Secondary wiki page fixture.',
                'position' => 2.5,
                'created_by' => 2,
                'last_edited_by' => 2,
                'created' => '2026-03-31 05:06:16',
                'modified' => '2026-03-31 05:06:16',
            ],
            [
                'id' => 3,
                'project_id' => 1,
                'parent_id' => null,
                'title' => 'Architecture',
                'slug' => 'architecture',
                'body' => 'Architecture wiki page fixture.',
                'position' => 3.5,
                'created_by' => 1,
                'last_edited_by' => 1,
                'created' => '2026-03-31 05:06:16',
                'modified' => '2026-03-31 05:06:16',
            ],
        ];
        parent::init();
    }
}
