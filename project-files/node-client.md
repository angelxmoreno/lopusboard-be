# Node HTTP Client

This document is where we should design the backend Node HTTP client before asking Gemini CLI to build it.

This is not the implementation task yet.

The purpose of this file is:
- define the shape we want
- record the decisions we make together
- give Gemini CLI a clear implementation brief once we are ready

Use this file together with `project-files/openapi.yaml`, which describes the current backend API in a machine-readable format.

## Goal

Create a small TypeScript HTTP client for this backend so frontend and tooling code do not keep re-implementing:
- base URL handling
- auth header handling
- JSON parsing
- normalized error handling
- endpoint-specific request helpers

## Current Working Assumptions

Unless we change direction later, the first version should assume:
- the client will live in this repo
- it will be a TypeScript package
- it will use `fetch`
- it will target Node first
- it should work in Bun too if that comes for free
- it should expose a service-style API, not just low-level request methods

## Backend Surface To Cover

The first version should cover the current API surface:
- identity
- projects
- project members
- statuses
- departments
- issues
- comments
- issue relations
- wiki pages
- wiki page revisions
- attachments
- activity log

It should also leave room for the next workflow endpoints:
- issue move/reorder
- wiki revision restore

## Design Questions To Finalize

### 1. Runtime

Questions:
- Node only?
- Node + Bun?
- browser-safe later?

Current recommendation:
- Node-first, `fetch`-based
- avoid runtime-specific dependencies unless we truly need them

### 2. Package Location

Questions:
- local package in this repo?
- separate repo later?

Current recommendation:
- create `packages/node-client` in this repo

### 3. Client Shape

Question:
- raw request client, or service-style resources?

Current recommendation:
- service-style resources

Example:

```ts
const client = new LopusboardClient({
  baseUrl: 'http://localhost:8765/api',
  getToken: async () => jwt,
});

await client.identity.me();
await client.projects.list();
await client.projects.get(1);
await client.projects.create(payload);
await client.issues.list();
await client.issues.get(1);
await client.issues.create(payload);
```

### 4. Authentication

Question:
- how should the token be supplied?

Current recommendation:
- support an optional token provider callback
- allow a static token too if that is easy

Preferred shape:

```ts
type TokenProvider = () => string | null | Promise<string | null>;

new LopusboardClient({
  baseUrl,
  getToken,
});
```

### 5. HTTP Caching And TanStack Query

Question:
- how should HTTP caching interact with TanStack Query?

Current recommendation:
- the client should support HTTP cache validation for `GET` requests
- the first version should be designed around:
  - `ETag`
  - `If-None-Match`
  - `304 Not Modified`
- TanStack Query should still own UI-level caching, freshness, and invalidation

Recommended split:
- the HTTP client owns transport-level cache validation
- TanStack Query owns in-memory app caching and refetch behavior

Desired behavior:
- TanStack Query calls the client as normal
- the client sends `If-None-Match` when it has an `ETag` for that request
- if the server returns new data, the client returns that data
- if the server returns `304`, the client returns the previously cached response body
- TanStack Query keeps the existing data and refreshes the query lifecycle without replacing the value

This means the client should eventually support:
- request-key-based `ETag` storage for `GET` requests
- request-key-based cached response body storage for `GET` requests
- a normalized result path for `304` handling

The client should not try to replace TanStack Query. It should only make conditional HTTP requests efficient.

### 6. Error Handling

Question:
- what should failed requests look like to callers?

Current recommendation:
- throw a normalized `ApiError`
- include `status`
- include parsed response body when possible
- keep the original message readable

### 7. Typing

Question:
- how much typing do we want in v1?

Current recommendation:
- hand-write important request/response types
- keep them small and aligned with real API payloads
- do not over-design shared schema generation yet

## Proposed Package Structure

```text
packages/node-client/
  package.json
  tsconfig.json
  src/
    index.ts
    client.ts
    errors.ts
    types.ts
    resources/
      identity.ts
      projects.ts
      project-members.ts
      statuses.ts
      departments.ts
      issues.ts
      comments.ts
      issue-relations.ts
      wiki-pages.ts
      wiki-page-revisions.ts
      attachments.ts
      activity-log.ts
```

## Responsibilities For V1

The client should:
- build URLs correctly
- send JSON requests
- attach bearer tokens
- support HTTP cache validation for `GET` requests
- parse JSON responses
- throw normalized errors
- expose resource-specific methods

The client should not try to solve yet:
- retries
- code generation
- framework-specific adapters
- advanced pagination abstractions

## Draft Gemini CLI Implementation Brief

When we are ready, Gemini CLI should be asked to:

1. Create `packages/node-client`.
2. Use TypeScript.
3. Use native `fetch`.
4. Create a base client with:
   - `baseUrl`
   - optional `getToken`
   - JSON request/response handling
   - normalized `ApiError`
   - `GET` request support for `ETag`, `If-None-Match`, and `304` reuse
5. Expose resource modules for:
   - identity
   - projects
   - project members
   - statuses
   - departments
   - issues
   - comments
   - issue relations
   - wiki pages
   - wiki page revisions
   - attachments
   - activity log
6. Add request/response types for the real API payloads in this repo.
7. Add a small test suite covering:
   - auth header injection
   - JSON parsing
   - error normalization
   - a few representative resource calls
8. Keep the package minimal and avoid speculative abstractions.
9. Add a short package README with example usage.

## Open Questions For Discussion

These are the things we should settle together before implementation:
- should the package be private to this repo or structured as a publishable package?
- do we want ESM only, or dual ESM/CJS?
- do we want resource methods named `get` / `list` / `create`, or something more explicit?
- do we want raw response access anywhere, or only parsed data?
- should request cancellation via `AbortSignal` be supported in v1?
- how much response typing should be strict vs pragmatic in v1?
- how visible should the HTTP cache layer be to callers, if at all?
