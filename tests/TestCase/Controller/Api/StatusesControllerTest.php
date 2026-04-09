<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Test\Support\Auth\TestIdentityProvider;
use App\Test\Support\Auth\TestIdentityResolver;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use IdentityBridge\Provider\ProviderInterface;
use IdentityBridge\Resolver\LocalUserResolverInterface;

class StatusesControllerTest extends TestCase
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

    public function testIndexReturnsOnlyStatusesFromAuthorizedProjects(): void
    {
        $this->mockService(
            LocalUserResolverInterface::class,
            fn(): LocalUserResolverInterface => new TestIdentityResolver(['id' => 2]),
        );

        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer valid-test-token',
            ],
        ]);

        $this->get('/api/statuses');

        $this->assertResponseOk();

        /** @var array<string, mixed> $payload */
        $payload = json_decode($this->_getBodyAsString(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertCount(2, $payload['data']);
        $this->assertSame([1, 3], array_column($payload['data'], 'id'));
    }

    public function testAddRejectsProjectMembersWhoAreNotAdmins(): void
    {
        $this->mockService(
            LocalUserResolverInterface::class,
            fn(): LocalUserResolverInterface => new TestIdentityResolver(['id' => 2]),
        );

        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer valid-test-token',
            ],
        ]);

        $this->post('/api/statuses', [
            'project_id' => 1,
            'name' => 'Blocked',
            'color' => '#111111',
            'position' => 4,
            'is_default' => false,
            'is_done' => false,
        ]);

        $this->assertResponseCode(403);
    }
}
