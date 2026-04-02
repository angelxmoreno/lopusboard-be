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
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.WikiPages',
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
     * @return void
     */
    public function testBeforeMarshalGeneratesSlug(): void
    {
        $page = $this->WikiPages->newEntity(
            [
                'project_id' => 1,
                'title' => 'Hello World',
                'created_by' => 1,
                'position' => 3.0,
            ],
            ['accessibleFields' => ['*' => true]],
        );

        $this->assertSame('hello-world', $page->get('slug'));
    }

    /**
     * @return void
     */
    public function testBuildRulesRejectParentFromAnotherProject(): void
    {
        $page = $this->WikiPages->newEntity(
            [
                'project_id' => 1,
                'parent_id' => 2,
                'title' => 'Cross Project Child',
                'created_by' => 1,
                'position' => 3.0,
            ],
            ['accessibleFields' => ['*' => true]],
        );

        $this->assertFalse($this->WikiPages->save($page));
        $this->assertArrayHasKey('parent_id', $page->getErrors());
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
