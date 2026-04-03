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
        $this->records = [];
        parent::init();
    }
}
