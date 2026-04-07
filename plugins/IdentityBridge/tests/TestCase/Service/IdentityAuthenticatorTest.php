<?php
declare(strict_types=1);

namespace IdentityBridge\Test\TestCase\Service;

use IdentityBridge\Exception\AuthenticationException;
use IdentityBridge\Provider\ProviderInterface;
use IdentityBridge\Resolver\LocalUserResolverInterface;
use IdentityBridge\Service\IdentityAuthenticator;
use IdentityBridge\ValueObject\AuthenticatedRequestIdentity;
use IdentityBridge\ValueObject\RemoteIdentity;
use PHPUnit\Framework\TestCase;
use stdClass;

class IdentityAuthenticatorTest extends TestCase
{
    public function testAuthenticateReturnsRemoteIdentityAndResolvedUser(): void
    {
        $jwt = 'provider.jwt.token';
        $remoteIdentity = new RemoteIdentity(
            provider: 'clerk',
            subject: 'user_123',
            email: 'demo@example.com',
            claims: ['sub' => 'user_123'],
        );
        $user = (object)[
            'id' => 42,
            'email' => 'demo@example.com',
        ];

        $provider = new class ($jwt, $remoteIdentity) implements ProviderInterface {
            public function __construct(
                private readonly string $expectedJwt,
                private readonly RemoteIdentity $identity,
            ) {
            }

            public function verify(string $jwt): RemoteIdentity
            {
                TestCase::assertSame($this->expectedJwt, $jwt);

                return $this->identity;
            }
        };

        $resolver = new class ($remoteIdentity, $user) implements LocalUserResolverInterface {
            public function __construct(
                private readonly RemoteIdentity $expectedIdentity,
                private readonly object $user,
            ) {
            }

            public function resolve(RemoteIdentity $identity): object
            {
                TestCase::assertSame($this->expectedIdentity, $identity);

                return $this->user;
            }
        };

        $result = (new IdentityAuthenticator($provider, $resolver))->authenticate($jwt);

        $this->assertInstanceOf(AuthenticatedRequestIdentity::class, $result);
        $this->assertSame($remoteIdentity, $result->remoteIdentity);
        $this->assertSame($user, $result->user);
    }

    public function testAuthenticateRejectsBlankBearerTokens(): void
    {
        $provider = new class implements ProviderInterface {
            public function verify(string $jwt): RemoteIdentity
            {
                TestCase::fail('Provider should not be called for blank tokens.');
            }
        };

        $resolver = new class implements LocalUserResolverInterface {
            public function resolve(RemoteIdentity $identity): object
            {
                TestCase::fail('Resolver should not be called for blank tokens.');
            }
        };

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Missing bearer token.');

        (new IdentityAuthenticator($provider, $resolver))->authenticate('   ');
    }

    public function testAuthenticateLetsProviderAuthenticationFailuresBubbleUp(): void
    {
        $provider = new class implements ProviderInterface {
            public function verify(string $jwt): RemoteIdentity
            {
                throw new AuthenticationException('Token verification failed.');
            }
        };

        $resolver = new class implements LocalUserResolverInterface {
            public function resolve(RemoteIdentity $identity): object
            {
                return new stdClass();
            }
        };

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Token verification failed.');

        (new IdentityAuthenticator($provider, $resolver))->authenticate('jwt');
    }
}
