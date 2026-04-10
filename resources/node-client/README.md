# LopusBoard Node Client (Internal)

An internal TypeScript HTTP client for the LopusBoard Backend API, designed for use with **Bun**.

## Features

- **🚀 Bun-Native:** Optimized for the Bun runtime using native `fetch`.
- **🛡️ Type-Safe:** Full TypeScript support for all API resources and models.
- **📦 Service-Style API:** Intuitive resource-based methods (e.g., `client.projects.list()`).
- **⚡ ETag Support:** Auth-aware HTTP cache validation using `ETag` and `If-None-Match`.
- **💎 Clean DX:** Normalized error handling via `ApiError`.

## Installation

This is an internal package. Ensure you have [Bun](https://bun.sh) installed.

```bash
bun install
```

## Usage

### Basic Setup

```typescript
import { LopusboardClient } from './src';

const client = new LopusboardClient({
  baseUrl: 'http://localhost:8080',
  getToken: async () => 'your-appwrite-jwt'
});

// Use resources
const projects = await client.projects.list();
```

### Authentication

Supports static strings or dynamic providers (async supported):

```typescript
// Dynamic (recommended for refreshing JWTs)
new LopusboardClient({
  baseUrl,
  getToken: async () => {
    const session = await appwrite.account.createJWT();
    return session.jwt;
  }
});
```

### Error Handling

```typescript
import { ApiError } from './src';

try {
  await client.projects.get(999);
} catch (error) {
  if (error instanceof ApiError) {
    console.error(`Status: ${error.status}`);
    console.error(`Message: ${error.message}`);
  }
}
```

## Development

- `bun test`: Run the test suite (fully mocked).
- `bun run check`: Run all quality checks (lint, types, duplication).
- `bun run lint`: Run Biome linter.

## Project Structure

```text
├── src/
│   ├── index.ts      # Main Client entry point
│   ├── client.ts     # Base HTTP transport logic
│   ├── types.ts      # API Type definitions
│   └── resources/    # Resource-specific modules
└── tests/            # Test suite
```

## License

Internal Project - All Rights Reserved
