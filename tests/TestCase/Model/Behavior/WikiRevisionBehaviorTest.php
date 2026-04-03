<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Behavior;

use App\Model\Table\WikiPagesTable;
use Cake\TestSuite\TestCase;

/**
 * WikiRevisionBehavior test case.
 */
class WikiRevisionBehaviorTest extends TestCase
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
    public function testBehaviorAttachedAndCreatesNewRevision(): void
    {
        $this->assertTrue($this->WikiPages->behaviors()->has('WikiRevision'));

        $page = $this->WikiPages->get(1);
        $page->body = 'Updated wiki body.';
        $page->set('last_edited_by', 1, ['guard' => false]);

        $saved = $this->WikiPages->save($page);

        $this->assertNotFalse($saved);
        $latestRevision = $this->WikiPages->WikiPageRevisions->find()
            ->where(['wiki_page_id' => 1])
            ->orderByDesc('revision_number')
            ->firstOrFail();

        $this->assertSame(2, (int)$latestRevision->revision_number);
        $this->assertSame('Updated wiki body.', $latestRevision->body);
        $this->assertSame(1, (int)$latestRevision->edited_by);
    }
}
