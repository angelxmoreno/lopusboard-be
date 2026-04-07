<?php
declare(strict_types=1);

namespace App\Test\Support\Auth;

use IdentityBridge\Resolver\LocalUserResolverInterface;
use IdentityBridge\ValueObject\RemoteIdentity;

class TestIdentityResolver implements LocalUserResolverInterface
{
    /**
     * @param \IdentityBridge\ValueObject\RemoteIdentity $identity The normalized identity.
     * @return object
     */
    public function resolve(RemoteIdentity $identity): object
    {
        return (object)[
            'id' => 42,
            'name' => $identity->displayName,
            'email' => $identity->email,
            'avatar_url' => $identity->avatarUrl,
        ];
    }
}
