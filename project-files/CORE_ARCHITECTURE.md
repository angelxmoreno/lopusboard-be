# Core Architecture (Backend API)

## Vision & Philosophy
LopusBoard is an **API-first**, project-isolated project management tool. The backend is a pure REST API; it does not serve HTML or manage styling.
- **Own your data:** Self-hosted, no vendor lock-in.
- **Project-isolated:** Every project is a silo (Issues, Wiki, Files, Team).
- **Agent-ready:** Structured API for AI agent interactions (Phase 2 includes MCP).

## Backend Tech Stack (Phase 1)
- **Language:** PHP 8.5.4
- **Framework:** CakePHP 5
- **Database:** MySQL 8
- **Auth:** Appwrite (JWT Verification via PHP SDK)
- **File Storage:** Google Drive API (Phase 1)

## Auth Flow (Appwrite Delegation)
The backend **never issues tokens**. It only verifies them.
1. Frontend sends JWT in `Authorization: Bearer <token>` header.
2. `AppwriteAuthMiddleware` extracts the token.
3. Call Appwrite PHP SDK to verify the token:
   ```php
   $client = (new Client())
       ->setEndpoint(env('APPWRITE_ENDPOINT'))
       ->setProject(env('APPWRITE_PROJECT_ID'))
       ->setJWT($token);
   $account = new Account($client);
   $appwriteUser = $account->get(); // throws if invalid
   ```
4. On success, sync to local `users` table via `appwrite_id`.
5. Attach the resolved `User` entity to the request as `identity`.

## User Sync Logic
- After verifying the JWT, check if a `users` row exists for `appwrite_id`.
- If not, create one (first login sync).
- On subsequent logins, optionally sync `name` and `email`.
