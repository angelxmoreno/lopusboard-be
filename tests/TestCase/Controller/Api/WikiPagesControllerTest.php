<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Table\WikiPagesTable;
use App\Test\Support\Auth\TestIdentityProvider;
use App\Test\Support\Auth\TestIdentityResolver;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use IdentityBridge\Provider\ProviderInterface;
use IdentityBridge\Resolver\LocalUserResolverInterface;

class WikiPagesControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Projects',
        'app.ProjectMembers',
        'app.ActivityLog',
        'app.WikiPageLinks',
        'app.WikiPages',
        'app.WikiPageRevisions',
    ];

    /**
     * @var array<string, mixed>
     */
    protected array $appPluginsToLoad = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockService(
            ProviderInterface::class,
            fn(): ProviderInterface => new TestIdentityProvider([
                'provider' => 'test-provider',
            ]),
        );
    }

    public function testIndexReturnsOnlyWikiPagesFromAuthorizedProjects(): void
    {
        $this->mockService(
            LocalUserResolverInterface::class,
            fn(): LocalUserResolverInterface => new TestIdentityResolver(['id' => 2]),
        );

        $this->configRequest([
            'headers' => ['Authorization' => 'Bearer valid-test-token'],
        ]);

        $this->get('/api/wiki-pages');

        $this->assertResponseOk();

        /** @var array<string, mixed> $payload */
        $payload = json_decode($this->_getBodyAsString(), true, 512, JSON_THROW_ON_ERROR);

        $ids = array_column($payload['data'], 'id');
        sort($ids);

        $this->assertSame([1, 3], $ids);
    }

    public function testAddUsesAuthenticatedUserAsCreator(): void
    {
        $this->mockService(
            LocalUserResolverInterface::class,
            fn(): LocalUserResolverInterface => new TestIdentityResolver(['id' => 2]),
        );

        $this->configRequest([
            'headers' => ['Authorization' => 'Bearer valid-test-token'],
        ]);

        $this->post('/api/wiki-pages', [
            'project_id' => 1,
            'title' => 'Created Through API',
            'body' => 'Hello docs',
            'position' => 4,
        ]);

        $this->assertResponseCode(201);

        /** @var array<string, mixed> $payload */
        $payload = json_decode($this->_getBodyAsString(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(4, $payload['data']['id']);
        $this->assertSame(2, $this->wikiPages()->get(4)->created_by);
    }

    private function wikiPages(): WikiPagesTable
    {
        /** @var \App\Model\Table\WikiPagesTable */
        return $this->getTableLocator()->get(WikiPagesTable::class);
    }
}
