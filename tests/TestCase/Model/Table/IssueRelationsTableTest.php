<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\IssueRelationsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\IssueRelationsTable Test Case
 */
class IssueRelationsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\IssueRelationsTable
     */
    protected $IssueRelations;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.IssueRelations',
        'app.Issues',
        'app.RelatedIssues',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('IssueRelations') ? [] : ['className' => IssueRelationsTable::class];
        $this->IssueRelations = $this->getTableLocator()->get('IssueRelations', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->IssueRelations);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\IssueRelationsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\IssueRelationsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
