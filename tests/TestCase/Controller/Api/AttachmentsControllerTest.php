<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Table\AttachmentsTable;
use App\Test\Support\Auth\TestIdentityProvider;
use App\Test\Support\Auth\TestIdentityResolver;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use IdentityBridge\Provider\ProviderInterface;
use IdentityBridge\Resolver\LocalUserResolverInterface;

class AttachmentsControllerTest extends TestCase
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
        'app.Attachments',
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

    public function testIndexReturnsOnlyAttachmentsFromAuthorizedProjects(): void
    {
        $this->mockService(
            LocalUserResolverInterface::class,
            fn(): LocalUserResolverInterface => new TestIdentityResolver(['id' => 2]),
        );

        $this->configRequest([
            'headers' => ['Authorization' => 'Bearer valid-test-token'],
        ]);

        $this->get('/api/attachments');

        $this->assertResponseOk();

        /** @var array<string, mixed> $payload */
        $payload = json_decode($this->_getBodyAsString(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame([1], array_column($payload['data'], 'id'));
    }

    public function testAddUsesAuthenticatedUserAsUploader(): void
    {
        $this->mockService(
            LocalUserResolverInterface::class,
            fn(): LocalUserResolverInterface => new TestIdentityResolver(['id' => 2]),
        );

        $this->configRequest([
            'headers' => ['Authorization' => 'Bearer valid-test-token'],
        ]);

        $this->post('/api/attachments', [
            'project_id' => 1,
            'storage_provider' => 'google_drive',
            'filename' => 'created-through-api.pdf',
        ]);

        $this->assertResponseCode(201);

        /** @var array<string, mixed> $payload */
        $payload = json_decode($this->_getBodyAsString(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(3, $payload['data']['id']);
        $this->assertSame(2, $this->attachments()->get(3)->uploaded_by);
    }

    private function attachments(): AttachmentsTable
    {
        /** @var \App\Model\Table\AttachmentsTable */
        return $this->getTableLocator()->get(AttachmentsTable::class);
    }
}
