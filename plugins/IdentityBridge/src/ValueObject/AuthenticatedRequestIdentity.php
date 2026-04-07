<?php
declare(strict_types=1);

namespace IdentityBridge\ValueObject;

/**
 * Bundles the normalized remote identity with the resolved local user.
 */
readonly class AuthenticatedRequestIdentity
{
    public function __construct(
        public RemoteIdentity $remoteIdentity,
        public object $user,
    ) {
    }
}
