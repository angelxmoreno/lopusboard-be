<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\WikiPageLinksTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\WikiPageLinksTable Test Case
 */
class WikiPageLinksTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\WikiPageLinksTable
     */
    protected $WikiPageLinks;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.WikiPageLinks',
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
        $config = $this->getTableLocator()->exists('WikiPageLinks') ? [] : ['className' => WikiPageLinksTable::class];
        $this->WikiPageLinks = $this->getTableLocator()->get('WikiPageLinks', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->WikiPageLinks);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\WikiPageLinksTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $link = $this->WikiPageLinks->newEntity(
            [
                'source_page_id' => null,
                'target_page_id' => null,
            ],
            ['accessibleFields' => ['*' => true]],
        );

        $this->assertArrayHasKey('source_page_id', $link->getErrors());
        $this->assertArrayHasKey('target_page_id', $link->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\WikiPageLinksTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $duplicate = $this->WikiPageLinks->newEntity(
            [
                'source_page_id' => 1,
                'target_page_id' => 1,
            ],
            ['accessibleFields' => ['*' => true]],
        );

        $this->assertFalse($this->WikiPageLinks->save($duplicate));
        $this->assertArrayHasKey('source_page_id', $duplicate->getErrors());
    }
}
