<?php
declare(strict_types=1);

namespace IdentityBridge\Test\Support;

use IdentityBridge\Resolver\LocalUserResolverInterface;
use IdentityBridge\ValueObject\RemoteIdentity;

final class TestResolver implements LocalUserResolverInterface
{
    /**
     * @param \IdentityBridge\ValueObject\RemoteIdentity $identity The normalized identity.
     * @return object
     */
    public function resolve(RemoteIdentity $identity): object
    {
        return (object)['id' => 1];
    }
}
