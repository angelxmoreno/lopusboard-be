<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * IssueRelationsFixture
 */
class IssueRelationsFixture extends TestFixture
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
                'issue_id' => 1,
                'related_issue_id' => 1,
                'type' => 'blocks',
                'created' => '2026-03-31 05:06:15',
                'modified' => '2026-03-31 05:06:15',
            ],
        ];
        parent::init();
    }
}
