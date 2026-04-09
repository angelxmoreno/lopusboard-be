<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Table\CommentsTable;
use App\Test\Support\Auth\TestIdentityProvider;
use App\Test\Support\Auth\TestIdentityResolver;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use IdentityBridge\Provider\ProviderInterface;
use IdentityBridge\Resolver\LocalUserResolverInterface;

class CommentsControllerTest extends TestCase
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
        'app.Comments',
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

    public function testAddUsesAuthenticatedUserAsCommentAuthor(): void
    {
        $this->mockService(
            LocalUserResolverInterface::class,
            fn(): LocalUserResolverInterface => new TestIdentityResolver(['id' => 2]),
        );

        $this->configRequest([
            'headers' => ['Authorization' => 'Bearer valid-test-token'],
        ]);

        $this->post('/api/comments', [
            'issue_id' => 1,
            'body' => 'Created through API',
            'user_id' => 999,
        ]);

        $this->assertResponseCode(201);

        /** @var array<string, mixed> $payload */
        $payload = json_decode($this->_getBodyAsString(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(2, $payload['data']['id']);
        $this->assertSame(2, $this->comments()->get(2)->user_id);
    }

    public function testAddRejectsUsersOutsideTheIssueProject(): void
    {
        $this->mockService(
            LocalUserResolverInterface::class,
            fn(): LocalUserResolverInterface => new TestIdentityResolver(['id' => 3]),
        );

        $this->configRequest([
            'headers' => ['Authorization' => 'Bearer valid-test-token'],
        ]);

        $this->post('/api/comments', [
            'issue_id' => 1,
            'body' => 'No access',
        ]);

        $this->assertResponseCode(403);
    }

    private function comments(): CommentsTable
    {
        /** @var \App\Model\Table\CommentsTable */
        return $this->getTableLocator()->get(CommentsTable::class);
    }
}
