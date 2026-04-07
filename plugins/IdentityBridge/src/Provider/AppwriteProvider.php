<?php
declare(strict_types=1);

namespace IdentityBridge\Provider;

use Appwrite\Client;
use Appwrite\Services\Account;
use IdentityBridge\ValueObject\RemoteIdentity;

class AppwriteProvider implements ProviderInterface
{
    public const PROVIDER_NAME = 'appwrite';

    protected string $endpoint;

    protected string $project;

    protected string $key;

    protected bool $isDev;

    /**
     * @param array{endpoint?: string, project?: string, key?: string, isDev?: bool} $config Provider config.
     */
    public function __construct(array $config = [])
    {
        $this->endpoint = (string)($config['endpoint'] ?? '');
        $this->project = (string)($config['project'] ?? '');
        $this->key = (string)($config['key'] ?? '');
        $this->isDev = (bool)($config['isDev'] ?? false);
    }

    /**
     * @throws \Appwrite\AppwriteException
     */
    protected function buildClient(string $jwt): Client
    {
        $client = new Client();

        $client
            ->setEndpoint($this->endpoint)
            ->setProject($this->project)
            ->setKey($this->key);

        if ($this->isDev) {
            $client->setSelfSigned();
        }

        $client->setJWT($jwt);

        return $client;
    }

    /**
     * Verifies a bearer token and returns normalized remote identity data.
     *
     * @param string $jwt The provider-issued bearer token.
     * @return \IdentityBridge\ValueObject\RemoteIdentity
     * @throws \Appwrite\AppwriteException
     */
    public function verify(string $jwt): RemoteIdentity
    {
        $client = $this->buildClient($jwt);
        $account = new Account($client);
        $user = $account->get();

        return new RemoteIdentity(
            provider: self::PROVIDER_NAME,
            subject: (string)$user->id,
            email: $user->email,
            emailVerified: $user->emailVerification,
            displayName: $user->name,
            claims: [
                'labels' => $user->labels,
            ],
        );
    }
}
