# Architecture

IdentityBridge should be built around four layers.

## 1. Provider Adapter

The provider adapter is responsible for remote auth verification only.

Responsibilities:
- accept a bearer token
- verify signature and claims
- reject expired or malformed tokens
- return a normalized remote identity object

Examples:
- `SupabaseProvider`
- `FirebaseProvider`
- `ClerkProvider`
- `AppwriteProvider`

The host app configures exactly one of these.

## 2. Remote Identity Normalization

Provider payloads differ, so the plugin needs one normalized value object such as:

- provider name
- external subject id
- email
- display name
- avatar url
- raw claims

This becomes the stable boundary between provider code and host app mapping code.

## 3. User Mapping

The plugin should not decide how a remote identity becomes a local user row.

Instead, the host app provides a mapper that turns normalized identity into local fields such as:

- `auth_provider`
- `provider_user_id`
- `email`
- `name`
- `avatar_url`

## 4. Local User Resolution And Request Identity

After mapping, the plugin calls a host-app resolver to:

- find an existing local user
- create one if missing
- optionally update selected fields on later requests or sign-ins

The resolved local user is then attached to the request so the rest of the app can authorize normally.
