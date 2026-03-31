<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ActivityLogTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ActivityLogTable Test Case
 */
class ActivityLogTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ActivityLogTable
     */
    protected $ActivityLog;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.ActivityLog',
        'app.Projects',
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('ActivityLog') ? [] : ['className' => ActivityLogTable::class];
        $this->ActivityLog = $this->getTableLocator()->get('ActivityLog', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->ActivityLog);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ActivityLogTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ActivityLogTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
