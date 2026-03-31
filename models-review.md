## Code Review

**Verdict**: REQUEST CHANGES  
**Confidence**: HIGH

### Findings

| Priority | Issue | Location |
|----------|-------|----------|
| P1 | Core domain rules from `project-files` are not implemented in the model layer, so the baked tables do not enforce project seeding, issue/task invariants, wiki revisioning, link syncing, or last-admin protection. | `src/Model/Table/ProjectsTable.php:46`, `src/Model/Table/IssuesTable.php:94`, `src/Model/Table/WikiPagesTable.php:76`, `src/Model/Table/ProjectMembersTable.php:67` |
| P1 | Required associations and custom finders from `project-files/MODEL_DEFINITIONS.md` are missing or named incompatibly, which will break the intended API query shapes and later business logic. | `src/Model/Table/UsersTable.php:41`, `src/Model/Table/IssuesTable.php:47`, `src/Model/Table/WikiPagesTable.php:43` |
| P1 | Several entities allow mass assignment of server-controlled fields like `project_id`, `created_by`, `uploaded_by`, `actor_id`, `created`, and `modified`, which lets API clients spoof ownership and audit metadata. | `src/Model/Entity/Project.php:38`, `src/Model/Entity/Issue.php:47`, `src/Model/Entity/WikiPage.php:39`, `src/Model/Entity/Attachment.php:38`, `src/Model/Entity/ActivityLog.php:36` |
| P1 | Validation and integrity rules are too weak for an API that must return `422` for invalid input; important foreign keys, enums, and scoped constraints are left to the database or not checked at all. | `src/Model/Table/ProjectsTable.php:119`, `src/Model/Table/IssuesTable.php:158`, `src/Model/Table/StatusesTable.php:66`, `src/Model/Table/AttachmentsTable.php:120`, `src/Model/Table/WikiPageRevisionsTable.php:94`, `src/Model/Table/ActivityLogTable.php:68`, `src/Model/Table/AttachmentLinksTable.php:62` |

### Details

#### [P1] Missing required domain behaviors and invariants
**Files:** `src/Model/Table/ProjectsTable.php:46`, `src/Model/Table/IssuesTable.php:94`, `src/Model/Table/WikiPagesTable.php:76`, `src/Model/Table/ProjectMembersTable.php:67`

The current classes are still scaffold-level tables. They do not implement the behavior explicitly required by `project-files/BUSINESS_LOGIC.md`: project seeding in `ProjectsTable::afterSave`, issue nesting and same-project inheritance checks, immutable `project_id`, auto-positioning, wiki slug generation, per-page revision creation, markdown link extraction, or last-admin protection for members. If the API is built on top of these tables as-is, invalid writes will be accepted until they hit DB constraints, and several required side effects will never happen.

**Suggested fix:** add domain rules in model callbacks/rules before any controller work depends on them.

#### [P1] Missing required associations and finders from the spec
**Files:** `src/Model/Table/UsersTable.php:41`, `src/Model/Table/IssuesTable.php:47`, `src/Model/Table/WikiPagesTable.php:43`

`project-files/MODEL_DEFINITIONS.md` calls out specific aliases and finders that are not present here. Examples:
- `UsersTable` is missing `Projects`, `Issues`, `AssignedIssues`, `WikiPages`, and `ActivityLog`.
- `IssuesTable` is missing `Creators`, `Tasks` (spec name), and the filtered `ActivityLog` association.
- `WikiPagesTable` is missing `Creators`, `LastEditors`, and uses `ParentWikiPages` / `ChildWikiPages` instead of the specified `ParentPages` / `ChildPages`.
- `findForKanban()` and `findTree()` are absent.

This matters because later code will either have to fight the baked naming or rewrite callers after the fact.

**Suggested fix:** align the association aliases and add the required custom finders now, before controllers and services begin depending on the baked names.

#### [P1] Entity accessibility allows ownership and audit spoofing
**Files:** `src/Model/Entity/Project.php:38`, `src/Model/Entity/Issue.php:47`, `src/Model/Entity/WikiPage.php:39`, `src/Model/Entity/Attachment.php:38`, `src/Model/Entity/ActivityLog.php:36`

These entities expose fields that should be server-derived from the authenticated identity or lifecycle logic. In this project, `created_by`, `uploaded_by`, `actor_id`, timestamps, and often `project_id` should not be trusted from request payloads. Leaving them mass assignable creates a straightforward path for forged audit history, cross-project writes, and incorrect ownership metadata.

**Suggested fix:** default `_accessible` to `false` for system-managed fields and only permit client-editable attributes like titles, descriptions, body text, and explicit user-managed lookup selections.

#### [P1] Validation and rules do not meet the API contract
**Files:** `src/Model/Table/ProjectsTable.php:119`, `src/Model/Table/IssuesTable.php:158`, `src/Model/Table/StatusesTable.php:66`, `src/Model/Table/AttachmentsTable.php:120`, `src/Model/Table/WikiPageRevisionsTable.php:94`, `src/Model/Table/ActivityLogTable.php:68`, `src/Model/Table/AttachmentLinksTable.php:62`

The API spec requires invalid requests to return `422`, but many important checks are either absent or deferred to the database:
- `ProjectsTable` does not validate `created_by` with `existsIn`.
- `IssuesTable` does not validate `priority`/`type` enums, `created_by`, or that `status_id` belongs to the same project.
- `StatusesTable` does not enforce the required hex color pattern or positive-decimal positioning.
- `AttachmentsTable` does not validate `uploaded_by`, `storage_provider`, or URL shape.
- `WikiPageRevisionsTable` does not validate `edited_by`.
- `ActivityLogTable` does not constrain `subject_type` / `action`.
- `AttachmentLinksTable` accepts any `linkable_type` and cannot verify that `linkable_id` points to an allowed record.

These gaps will either produce SQL exceptions or silently allow invalid state, both of which violate the documented backend contract.

**Suggested fix:** add explicit validator `inList`, regex, URL, and `existsIn` checks, then implement scoped custom rules for cross-project integrity.

### Recommendation

Do not build controllers on top of these tables yet. First bring `src/Model` into alignment with `project-files/MODEL_DEFINITIONS.md` and `project-files/BUSINESS_LOGIC.md`: fix association aliases, lock down entity accessibility, then add the required validations, scoped rules, callbacks, and custom finders.
