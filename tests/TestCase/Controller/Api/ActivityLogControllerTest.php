<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Table\ActivityLogTable;
use App\Test\Support\Auth\TestIdentityProvider;
use App\Test\Support\Auth\TestIdentityResolver;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use IdentityBridge\Provider\ProviderInterface;
use IdentityBridge\Resolver\LocalUserResolverInterface;

class ActivityLogControllerTest extends TestCase
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

        $entries = $this->activityLog()->newEntities([
            [
                'project_id' => 1,
                'actor_id' => 1,
                'subject_type' => 'attachment',
                'subject_id' => 1,
                'action' => 'created',
            ],
            [
                'project_id' => 2,
                'actor_id' => 2,
                'subject_type' => 'attachment',
                'subject_id' => 2,
                'action' => 'created',
            ],
        ], [
            'accessibleFields' => [
                'project_id' => true,
                'actor_id' => true,
                'subject_type' => true,
                'subject_id' => true,
                'action' => true,
            ],
        ]);

        $this->activityLog()->saveManyOrFail($entries);
    }

    public function testIndexReturnsOnlyActivityFromAuthorizedProjects(): void
    {
        $this->mockService(
            LocalUserResolverInterface::class,
            fn(): LocalUserResolverInterface => new TestIdentityResolver(['id' => 2]),
        );

        $this->configRequest([
            'headers' => ['Authorization' => 'Bearer valid-test-token'],
        ]);

        $this->get('/api/activity-log');

        $this->assertResponseOk();

        /** @var array<string, mixed> $payload */
        $payload = json_decode($this->_getBodyAsString(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame([1], array_column($payload['data'], 'project_id'));
    }

    private function activityLog(): ActivityLogTable
    {
        /** @var \App\Model\Table\ActivityLogTable */
        return $this->getTableLocator()->get(ActivityLogTable::class);
    }
}
