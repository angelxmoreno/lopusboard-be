<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\WikiPageRevisionsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\WikiPageRevisionsTable Test Case
 */
class WikiPageRevisionsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\WikiPageRevisionsTable
     */
    protected $WikiPageRevisions;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.WikiPageRevisions',
        'app.WikiPages',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('WikiPageRevisions') ? [] : ['className' => WikiPageRevisionsTable::class];
        $this->WikiPageRevisions = $this->getTableLocator()->get('WikiPageRevisions', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->WikiPageRevisions);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\WikiPageRevisionsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\WikiPageRevisionsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
