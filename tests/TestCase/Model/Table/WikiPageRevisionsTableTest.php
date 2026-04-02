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
    public function testInitializeAssociations(): void
    {
        $this->assertSame('Users', $this->WikiPageRevisions->getAssociation('Editors')->getClassName());
        $this->assertSame('edited_by', $this->WikiPageRevisions->getAssociation('Editors')->getForeignKey());
    }

    /**
     * @return void
     */
    public function testBuildRulesRejectDuplicateRevisionNumberPerPage(): void
    {
        $revision = $this->WikiPageRevisions->newEntity(
            [
                'wiki_page_id' => 1,
                'body' => 'Duplicate revision',
                'revision_number' => 1,
                'edited_by' => 1,
            ],
            ['accessibleFields' => ['*' => true]],
        );

        $this->assertFalse($this->WikiPageRevisions->save($revision));
        $this->assertArrayHasKey('wiki_page_id', $revision->getErrors());
    }
}
