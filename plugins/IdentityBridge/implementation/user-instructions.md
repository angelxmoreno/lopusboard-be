# IdentityBridge Build Instructions

This file is the human-facing build guide for the `IdentityBridge` plugin. Use it as a step-by-step implementation handoff for a junior developer.

The goal is to build a CakePHP plugin that:

- accepts a bearer JWT from the frontend
- verifies that JWT against one configured remote auth provider
- normalizes the remote identity into a stable plugin shape
- hands that identity to the host app
- lets the host app map and resolve the local user
- attaches the local user to the request

This plugin should support one configured provider per app. It should not try to auto-detect providers per request.

## Before You Start

Read these docs first:

1. [README.md](/Users/amoreno/ClaudeDesktopDropBox/lopusboard/lopusboard-be/plugins/IdentityBridge/README.md)
2. [architecture.md](/Users/amoreno/ClaudeDesktopDropBox/lopusboard/lopusboard-be/plugins/IdentityBridge/implementation/architecture.md)
3. [contracts.md](/Users/amoreno/ClaudeDesktopDropBox/lopusboard/lopusboard-be/plugins/IdentityBridge/implementation/contracts.md)

Do not start with provider-specific code. Build the shared plugin contracts first.

## Step 1: Create The Core Types

Create these files:

- `plugins/IdentityBridge/src/ValueObject/RemoteIdentity.php`
- `plugins/IdentityBridge/src/Exception/AuthenticationException.php`
- `plugins/IdentityBridge/src/Exception/ConfigurationException.php`

Purpose of each class:

- `RemoteIdentity`: immutable normalized identity object shared across the plugin
- `AuthenticationException`: exception for invalid or unverifiable tokens
- `ConfigurationException`: exception for invalid plugin or provider setup

What to build:

- `RemoteIdentity` should be an immutable value object.
- It should represent the normalized result of a verified remote user.
- It should not depend on any one provider's raw payload shape.

Minimum `RemoteIdentity` fields:

- `provider`
- `subject`
- `email`
- `emailVerified`
- `displayName`
- `avatarUrl`
- `claims`

Acceptance criteria:

- the object can be constructed with all expected fields
- the object exposes getters or readonly properties
- there is no provider-specific logic in this class

## Step 2: Define The Contracts

Create these files:

- `plugins/IdentityBridge/src/Provider/ProviderInterface.php`
- `plugins/IdentityBridge/src/Resolver/LocalUserResolverInterface.php`

Purpose of each class:

- `ProviderInterface`: contract for JWT verification plus remote identity normalization
- `LocalUserResolverInterface`: host-app contract for mapping normalized identity and returning the local user

What to build:

- `ProviderInterface` verifies a JWT and returns `RemoteIdentity`
- `LocalUserResolverInterface` returns the local app user after the host app maps and resolves it

Acceptance criteria:

- contracts are small and clear
- method names communicate responsibility
- interfaces depend on `RemoteIdentity`, not raw arrays from providers

## Step 3: Define The Local User Resolver Boundary

Create:

- `plugins/IdentityBridge/src/Resolver/LocalUserResolverInterface.php`

Purpose of this class:

- `LocalUserResolverInterface`: gives the plugin a stable way to ask the host app for the local user without owning the app’s persistence rules

What it should do:

1. accept `RemoteIdentity`
2. let the host app map identity fields into the local user shape
3. allow the host app to look up the local user
4. allow the host app to create the user if missing
5. allow the host app to update selected fields if needed
6. return the resolved local user entity or object

Important:

- do not implement this as package-owned local user persistence
- the plugin should define the interface only
- the host app owns the implementation

Acceptance criteria:

- the interface is small and explicit
- the package does not assume a `UsersTable` schema
- the host app has one clear place to implement local user mapping, lookup, create, and update logic

## Step 4: Build The Middleware

Create:

- `plugins/IdentityBridge/src/Middleware/IdentityBridgeMiddleware.php`

Purpose of this class:

- `IdentityBridgeMiddleware`: central request entry point for token extraction, verification, local user resolution, and request identity attachment

What it should do:

1. read the `Authorization` header
2. extract the bearer token
3. reject missing or malformed tokens
4. pass the token to the configured provider
5. receive `RemoteIdentity`
6. call the configured local user resolver
7. attach both the remote identity and local user to the request
8. continue to the next middleware

Request attributes to set:

- `identityBridge.remoteIdentity`
- `identityBridge.user`

Do not:

- make authorization decisions here
- embed provider-specific verification logic directly in the middleware

Acceptance criteria:

- missing token returns `401`
- invalid token returns `401`
- valid token attaches the local user to the request

## Step 5: Register Plugin Services

Update:

- `plugins/IdentityBridge/src/IdentityBridgePlugin.php`

Purpose of this class:

- `IdentityBridgePlugin`: plugin entrypoint for container registrations and optional middleware wiring

What to add:

- container registrations for the provider implementation
- container registration for the local user resolver
- middleware registration if the plugin is going to self-register it

Important:

- keep the plugin generic
- the host app should be able to swap provider and local user resolver implementations through configuration or DI bindings

Acceptance criteria:

- the plugin can resolve all required services from the container
- there is a clear place for the host app to override provider and resolver bindings

## Step 6: Implement One Real Provider

Do not implement all providers first.

Pick one provider and prove the plugin works end to end. Good first choices:

- Appwrite
- Clerk
- Supabase
- Firebase

Create one file such as:

- `plugins/IdentityBridge/src/Provider/AppwriteProvider.php`

Purpose of this class:

- `<ProviderName>Provider`: provider-specific adapter that verifies tokens and converts claims into `RemoteIdentity`

What it should do:

- verify the JWT
- extract the user identity
- normalize provider-specific claims into `RemoteIdentity`

Important:

- this is the only layer that should know provider claim names
- if the provider supports JWKS verification, prefer that over per-request remote fetches unless the provider requires otherwise

Acceptance criteria:

- valid provider token returns `RemoteIdentity`
- expired or invalid token throws `AuthenticationException`

## Step 7: Add Tests As You Build

Add tests for each layer while building it.

Recommended test files:

- `tests/TestCase/ValueObject/RemoteIdentityTest.php`
- `tests/TestCase/Provider/...`
- `tests/TestCase/Middleware/IdentityBridgeMiddlewareTest.php`

What to test:

- `RemoteIdentity` stores expected values
- provider adapter normalizes claims correctly
- middleware passes normalized identity to the local user resolver correctly
- middleware rejects bad tokens and accepts valid ones

Do not leave the middleware untested. That is the main integration point.

## Step 8: Define The Host App Resolution Contract

Once the plugin core works, document what the host app must provide.

At minimum, the host app must define:

- which provider to use
- how the host app resolves the local user from `RemoteIdentity`
- how the host app maps `RemoteIdentity` into the local user shape
- which local fields are immutable
- which local fields may be refreshed from remote identity

If the app still uses `appwrite_id`, call that out clearly. The current app schema is too provider-specific for a reusable bridge plugin.

## Step 9: Integrate Into The Host App

After the plugin works in isolation:

1. update the host app user schema if needed
2. load the plugin
3. place the middleware into the API stack
4. remove old provider-specific auth code from the app
5. verify authenticated requests resolve a local user

Do not mix old and new auth flows longer than necessary.

## Definition Of Done

The plugin is done when all of these are true:

- a request with a valid bearer token resolves a verified `RemoteIdentity`
- the plugin passes that identity into the host app’s local user resolver correctly
- the request carries the resolved local user for downstream app code
- invalid tokens return `401`
- tests cover provider verification and middleware behavior

## Suggested Delivery Order

Use this commit order:

1. `feat: add identity bridge core value objects and contracts`
2. `feat: add identity bridge local user resolver contract`
3. `feat: add identity bridge auth middleware`
4. `feat: register identity bridge services in plugin container`
5. `feat: add <provider> adapter for identity bridge`
6. `test: add identity bridge middleware and provider coverage`
7. `docs: document host app integration for identity bridge`

## Common Mistakes To Avoid

- putting provider-specific claim parsing in middleware
- letting the plugin depend directly on the host app `UsersTable` shape
- treating local-user lookup and persistence as package-owned logic
- using raw claim arrays everywhere instead of `RemoteIdentity`
- blending authentication and authorization
- trying to support all providers before the first one works end to end
