<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\StatusesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\StatusesTable Test Case
 */
class StatusesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\StatusesTable
     */
    protected $Statuses;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Statuses',
        'app.Projects',
        'app.Issues',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Statuses') ? [] : ['className' => StatusesTable::class];
        $this->Statuses = $this->getTableLocator()->get('Statuses', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Statuses);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\StatusesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $status = $this->Statuses->newEntity(
            [
                'project_id' => 1,
                'name' => 'Broken Status',
                'color' => 'blue',
                'position' => 0,
                'is_default' => false,
                'is_done' => false,
            ],
            ['accessibleFields' => ['*' => true]],
        );

        $this->assertArrayHasKey('color', $status->getErrors());
        $this->assertArrayHasKey('position', $status->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\StatusesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $status = $this->Statuses->newEntity(
            [
                'project_id' => 999,
                'name' => 'Unknown Project',
                'color' => '#111111',
                'position' => 1.0,
                'is_default' => false,
                'is_done' => false,
            ],
            ['accessibleFields' => ['*' => true]],
        );

        $this->assertFalse($this->Statuses->save($status));
        $this->assertArrayHasKey('project_id', $status->getErrors());
    }
}
