# Business Logic & Validation

Application-layer rules enforced by CakePHP before database writes.

## Project Seeding (afterSave)
When a project is created, insert default `statuses`, `departments`, and the `admin` record. Implement in `ProjectsTable.php` via `afterSave`.
- **Statuses:** Backlog (1.0), Todo (2.0, default), In Progress (3.0), In Review (4.0), Done (5.0, is_done).
- **Departments:** Engineering (1.0), Design (2.0), Product (3.0), Marketing (4.0).
- **Admin:** Creator automatically added to `project_members` with `admin` role.

## Issues & Tasks
- **Nesting:** Max one level. An issue can have tasks; a task **cannot** have sub-tasks.
- **Inheritance:** A task's `project_id` must match its parent issue's `project_id`.
- **Immutability:** `project_id` is immutable after creation.
- **Positioning:** New issues are appended at `MAX(position) + 1`.

## Wiki Pages
- **Slug generation:** Auto-generated from title on create. Unique within project.
- **Revisions:** On every save, insert a row in `wiki_page_revisions`. Revision number increments **per page**.
- **Page Links:** Parse markdown body for internal links (`[[Page Title]]`) and update `wiki_page_links`.

## Activity Log Events
Write a log row for every mutation:
- `created`, `updated`, `deleted`
- `status_changed`, `assigned`, `unassigned`
- `priority_changed`, `moved`, `relation_added`
- Store `old_value` and `new_value` as JSON.

## Core Validation Rules (CakePHP)
All errors must return `422 Unprocessable Entity` with JSON.
- **Users:** `appwrite_id` (required, unique), `email` (valid format).
- **Statuses:** `color` (matches `^#[0-9A-Fa-f]{6}$`), `position` (positive decimal).
- **Issues:** `priority` (low, medium, high, critical), `status_id` (must belong to same project).
- **Wiki:** `slug` (auto-generated, unique within project).
- **Members:** Last admin protection (must have at least one admin).
