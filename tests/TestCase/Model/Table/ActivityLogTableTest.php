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
        $activityLog = $this->ActivityLog->newEntity(
            [
                'project_id' => 1,
                'actor_id' => 1,
                'subject_type' => 'project',
                'subject_id' => 1,
                'action' => 'renamed',
                'old_value' => '{bad json',
            ],
            ['accessibleFields' => ['*' => true]],
        );

        $this->assertArrayHasKey('subject_type', $activityLog->getErrors());
        $this->assertArrayHasKey('action', $activityLog->getErrors());
        $this->assertArrayHasKey('old_value', $activityLog->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ActivityLogTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $activityLog = $this->ActivityLog->newEntity(
            [
                'project_id' => 999,
                'actor_id' => 1,
                'subject_type' => 'issue',
                'subject_id' => 1,
                'action' => 'created',
            ],
            ['accessibleFields' => ['*' => true]],
        );

        $this->assertFalse($this->ActivityLog->save($activityLog));
        $this->assertArrayHasKey('project_id', $activityLog->getErrors());
    }
}
