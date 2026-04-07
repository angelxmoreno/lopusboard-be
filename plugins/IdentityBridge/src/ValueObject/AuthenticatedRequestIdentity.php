<?php
declare(strict_types=1);

namespace IdentityBridge\ValueObject;

/**
 * Bundles the normalized remote identity with the resolved local user.
 */
readonly class AuthenticatedRequestIdentity
{
    /**
     * @param \IdentityBridge\ValueObject\RemoteIdentity $remoteIdentity The normalized remote identity.
     * @param object $user The resolved local user object.
     */
    public function __construct(
        public RemoteIdentity $remoteIdentity,
        public object $user,
    ) {
    }
}
