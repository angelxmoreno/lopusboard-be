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
}
