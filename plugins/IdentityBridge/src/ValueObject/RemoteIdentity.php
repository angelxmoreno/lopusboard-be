<?php
declare(strict_types=1);

namespace IdentityBridge\ValueObject;

/**
 * Normalized remote identity returned by an auth provider adapter.
 *
 * @property-read array<string, mixed> $claims
 */
readonly class RemoteIdentity
{
    /**
     * @param array<string, mixed> $claims Provider-specific claims payload.
     */
    public function __construct(
        public string $provider,
        public string $subject,
        public ?string $email = null,
        public bool $emailVerified = false,
        public ?string $displayName = null,
        public ?string $avatarUrl = null,
        public array $claims = [],
    ) {
    }
}
