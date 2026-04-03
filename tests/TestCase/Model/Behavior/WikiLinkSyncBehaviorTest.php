<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Behavior;

use App\Model\Table\WikiPagesTable;
use Cake\TestSuite\TestCase;

/**
 * WikiLinkSyncBehavior test case.
 */
class WikiLinkSyncBehaviorTest extends TestCase
{
    /**
     * @var \App\Model\Table\WikiPagesTable
     */
    protected WikiPagesTable $WikiPages;

    /**
     * @var array<string>
     */
    protected array $fixtures = [
        'app.ActivityLog',
        'app.WikiPages',
        'app.WikiPageLinks',
        'app.WikiPageRevisions',
        'app.Projects',
        'app.Users',
    ];

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->WikiPages = $this->getTableLocator()->get('WikiPages');
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->WikiPages);

        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testBehaviorAttachedAndSyncsResolvedLinks(): void
    {
        $this->assertTrue($this->WikiPages->behaviors()->has('WikiLinkSync'));

        $page = $this->WikiPages->get(1);
        $page->body = 'See [[Architecture]] and [[Architecture]] again.';

        $saved = $this->WikiPages->save($page);

        $this->assertNotFalse($saved);
        $links = $this->WikiPages->WikiPageLinks->find()
            ->where(['source_page_id' => 1])
            ->orderByAsc('target_page_id')
            ->all()
            ->extract('target_page_id')
            ->toList();

        $this->assertSame([3], array_map('intval', $links));
    }
}
