<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\WikiPagesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\WikiPagesTable Test Case
 */
class WikiPagesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\WikiPagesTable
     */
    protected $WikiPages;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.WikiPages',
        'app.Projects',
        'app.WikiPageRevisions',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('WikiPages') ? [] : ['className' => WikiPagesTable::class];
        $this->WikiPages = $this->getTableLocator()->get('WikiPages', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->WikiPages);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\WikiPagesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\WikiPagesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
