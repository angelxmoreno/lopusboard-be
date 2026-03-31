# API Specification

Prefix: `/api`. All requests (except public) require a valid Appwrite JWT in `Authorization: Bearer <token>`.

## Standard Responses
- **Success:** `200 OK` (read/update), `201 Created` (create), `204 No Content` (delete).
- **Errors:** `400` Bad Request, `401` Unauthorized, `403` Forbidden, `404` Not Found, `422` Validation Failed.
- **Pagination:** `?page=1&limit=25` returns a `meta` object.
- **Timestamps:** ISO 8601 UTC.

## Endpoints

### 1. Health & Current User
- `GET /api/health` — Returns `{ "status": "ok" }`. (Public)
- `GET /api/me` — Returns the current authenticated user.
- `PATCH /api/me` — Update own name or avatar_url.

### 2. Projects & Members
- `GET /api/projects` — List projects the user belongs to.
- `POST /api/projects` — Create a project (triggers seeding).
- `GET /api/projects/:id` — Get a single project.
- `GET /api/projects/:id/members` — List members.
- `POST /api/projects/:id/members` — Add member (admin only).

### 3. Lookups (Statuses & Departments)
- `GET /api/projects/:id/statuses` — List columns.
- `PATCH /api/projects/:id/statuses/reorder` — Bulk update positions.
- `GET /api/projects/:id/departments` — List departments.

### 4. Issues & Tasks
- `GET /api/projects/:id/issues` — List (supports filters: `type`, `status_id`, `assignee_id`).
- `POST /api/projects/:id/issues` — Create (issue/task).
- `GET /api/projects/:id/issues/:issueId` — Detailed view with comments, attachments, activity.
- `PATCH /api/projects/:id/issues/:issueId` — Update fields.
- `PATCH /api/projects/:id/issues/:issueId/move` — Update kanban position.

### 5. Wiki
- `GET /api/projects/:id/wiki` — Tree structure of all pages.
- `POST /api/projects/:id/wiki` — Create a page.
- `GET /api/projects/:id/wiki/:pageId/revisions` — List versions.
- `POST /api/projects/:id/wiki/:pageId/revisions/:revId/restore` — Revert to a version.

### 6. Attachments
- `POST /api/projects/:id/attachments` — Register metadata from Google Drive.
- `POST /api/projects/:id/attachments/:attachmentId/link` — Link file to issue or wiki page.
- `GET /api/projects/:id/activity` — Project-wide audit feed.
