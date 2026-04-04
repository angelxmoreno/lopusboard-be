# IdentityBridge

IdentityBridge is a CakePHP plugin for applications that delegate user authentication to a single external identity provider such as Supabase, Firebase, Clerk, or Appwrite.

The frontend owns login state and sends a bearer token with each request. The backend uses IdentityBridge to:

1. read the incoming JWT
2. verify it against the configured provider
3. fetch or normalize the remote user
4. pass the normalized identity into the host app
5. let the host app map and resolve the local user record
6. attach the resolved local user to the request

This plugin is intentionally built around one configured provider per app. It does not try to detect the provider per request.

## Goals

- keep remote identity concerns outside the host application
- support provider-specific adapters behind one plugin contract
- keep the plugin API small enough for fast adoption
- make local authorization independent from remote authentication

## Non-Goals

- frontend login UI
- session-based CakePHP authentication
- multi-provider routing inside a single app
- project membership or application authorization rules

## Proposed Shape

- `Middleware/IdentityBridgeMiddleware.php`
  Verifies the bearer token and resolves the authenticated local user.
- `Provider/ProviderInterface.php`
  Contract for verifying a token and returning normalized remote identity data.
- `Resolver/LocalUserResolverInterface.php`
  Host-app contract for mapping remote identity and finding, creating, or updating the local user.
- `ValueObject/RemoteIdentity.php`
  Normalized identity returned by provider adapters.

## Host App Responsibilities

- choose one provider adapter
- provide provider-specific configuration
- implement `LocalUserResolverInterface`
- use the resolved local user for authorization and domain rules

## Implementation Docs

Build notes live in [implementation/README.md](/Users/amoreno/ClaudeDesktopDropBox/lopusboard/lopusboard-be/plugins/IdentityBridge/implementation/README.md).
