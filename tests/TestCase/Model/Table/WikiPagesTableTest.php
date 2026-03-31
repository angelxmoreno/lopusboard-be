<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\WikiPagesTable;
use Cake\Database\ValueBinder;
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
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('WikiPages') ? [] : ['className' => WikiPagesTable::class];
        $this->WikiPages = $this->getTableLocator()->get('WikiPages', $config);
        $this->WikiPages->setSchema([
            'id' => ['type' => 'integer'],
            'project_id' => ['type' => 'integer'],
            'position' => ['type' => 'decimal'],
        ]);
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
    public function testInitializeAssociations(): void
    {
        $this->assertSame('Users', $this->WikiPages->getAssociation('Creators')->getClassName());
        $this->assertSame('created_by', $this->WikiPages->getAssociation('Creators')->getForeignKey());
        $this->assertSame('Users', $this->WikiPages->getAssociation('LastEditors')->getClassName());
        $this->assertSame('last_edited_by', $this->WikiPages->getAssociation('LastEditors')->getForeignKey());
        $this->assertSame('WikiPages', $this->WikiPages->getAssociation('ParentPages')->getClassName());
        $this->assertSame('WikiPages', $this->WikiPages->getAssociation('ChildPages')->getClassName());
        $this->assertSame('source_page_id', $this->WikiPages->getAssociation('WikiPageLinks')->getForeignKey());
    }

    /**
     * Test findTree method
     *
     * @return void
     * @link \App\Model\Table\WikiPagesTable::findTree()
     */
    public function testFindTree(): void
    {
        $query = $this->WikiPages->findTree($this->WikiPages->find(), ['project_id' => 1]);
        $where = $query->clause('where')->sql(new ValueBinder());
        $order = $query->clause('order')->sql(new ValueBinder());

        $this->assertStringContainsString('WikiPages.project_id', $where);
        $this->assertStringContainsString('WikiPages.position', $order);
    }
}
