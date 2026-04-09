<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Table\IssuesTable;
use App\Test\Support\Auth\TestIdentityProvider;
use App\Test\Support\Auth\TestIdentityResolver;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use IdentityBridge\Provider\ProviderInterface;
use IdentityBridge\Resolver\LocalUserResolverInterface;

class IssuesControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Projects',
        'app.ProjectMembers',
        'app.Statuses',
        'app.Departments',
        'app.Issues',
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

    public function testIndexReturnsOnlyIssuesFromAuthorizedProjects(): void
    {
        $this->mockService(
            LocalUserResolverInterface::class,
            fn(): LocalUserResolverInterface => new TestIdentityResolver(['id' => 2]),
        );

        $this->configRequest([
            'headers' => ['Authorization' => 'Bearer valid-test-token'],
        ]);

        $this->get('/api/issues');

        $this->assertResponseOk();

        /** @var array<string, mixed> $payload */
        $payload = json_decode($this->_getBodyAsString(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame([1, 3], array_column($payload['data'], 'id'));
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

        $this->post('/api/issues', [
            'project_id' => 1,
            'type' => 'issue',
            'title' => 'Created Through API',
            'status_id' => 1,
            'priority' => 'medium',
            'position' => 4,
            'created_by' => 999,
        ]);

        $this->assertResponseCode(201);

        /** @var array<string, mixed> $payload */
        $payload = json_decode($this->_getBodyAsString(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(4, $payload['data']['id']);
        $this->assertSame(2, $this->issues()->get(4)->created_by);
    }

    private function issues(): IssuesTable
    {
        /** @var \App\Model\Table\IssuesTable */
        return $this->getTableLocator()->get(IssuesTable::class);
    }
}
