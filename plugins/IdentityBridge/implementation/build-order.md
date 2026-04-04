# Build Order

Build the plugin in this order so dependencies land first.

## 1. Core Value Object And Exceptions

Create:

- `src/ValueObject/RemoteIdentity.php`
- `src/Exception/AuthenticationException.php`
- `src/Exception/ConfigurationException.php`

Goal:
- establish the stable internal shape of verified identity

Class purposes:
- `RemoteIdentity`: immutable normalized identity returned by provider adapters
- `AuthenticationException`: signals invalid, expired, or unverifiable tokens
- `ConfigurationException`: signals broken plugin or provider configuration

## 2. Provider Contract

Create:

- `src/Provider/ProviderInterface.php`

Goal:
- define the boundary every provider adapter must implement

Class purpose:
- `ProviderInterface`: verifies a JWT and returns `RemoteIdentity`

## 3. Mapper And Local User Resolver Contracts

Create:

- `src/Mapper/UserMapperInterface.php`
- `src/Resolver/LocalUserResolverInterface.php`

Goal:
- define how normalized identity becomes host-app user data and how the host app returns the local user

Class purposes:
- `UserMapperInterface`: maps `RemoteIdentity` into the host app’s local user payload
- `LocalUserResolverInterface`: host-app contract for finding, creating, or updating the local user

## 4. Middleware

Create:

- `src/Middleware/IdentityBridgeMiddleware.php`

Goal:
- verify incoming tokens and attach the resolved local user to the request

Class purpose:
- `IdentityBridgeMiddleware`: orchestrates token extraction, provider verification, local user resolution, and request attribute assignment

## 5. Container Wiring

Update:

- `src/IdentityBridgePlugin.php`

Goal:
- register provider, mapper, and local user resolver dependencies cleanly

## 6. First Provider Adapter

Create one provider first, not all four.

Examples:
- `src/Provider/AppwriteProvider.php`
- `src/Provider/ClerkProvider.php`

Goal:
- prove the contract works end-to-end with one real provider

Class purpose:
- `<ProviderName>Provider`: contains provider-specific JWT verification and claim normalization

## 7. Tests

Add:

- unit tests for the provider adapter
- unit tests for the mapper
- unit tests for the host app’s local user resolver implementation
- middleware/integration tests for authenticated requests

Goal:
- keep verification, mapping, and local-user resolution behavior stable

## 8. Host App Integration

After the plugin is working:

- update the app schema if needed
- wire the plugin middleware into the API stack
- remove old provider-specific auth code from the app
