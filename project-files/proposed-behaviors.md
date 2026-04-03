# Proposed Behaviors

This file narrows the earlier `model-review/core-domain-rules.md` notes into a concrete behavior proposal. The goal is to keep table classes focused on validation, associations, and small integrity rules while moving side effects and lifecycle policy into reusable behaviors.

Implementation note:
- these behaviors are now implemented in `src/Model/Behavior`
- the table wiring lives in `src/Model/Table`
- the tests for them live under `tests/TestCase/Model/Behavior` and `tests/TestCase/Model/Table`

## 1. `ProjectSeedingBehavior`

### Why we need it
Creating a project should do more than insert one row. The project needs an initial working setup so the rest of the app can function immediately:
- default statuses
- default departments
- an admin membership for the creator

This is multi-table setup logic, so it is cleaner in a behavior than in a large `ProjectsTable::afterSave()`.

### Models that would use it
- `ProjectsTable`

### Tables it would write to
- `Projects`
- `Statuses`
- `Departments`
- `ProjectMembers`

## 2. `IssueLifecycleBehavior`

### Why we need it
Issues and tasks have several lifecycle rules that go beyond simple validation:
- inherit `project_id` from the parent issue when creating a task
- keep `project_id` immutable after create
- auto-assign `position` for new issues
- enforce task-specific save rules consistently

These rules are tightly related and all happen around save operations, which makes them a good fit for one lifecycle behavior.

### Models that would use it
- `IssuesTable`

### Tables it would read or write
- `Issues`
- `Statuses` when validating project-scoped workflow state

## 3. `WikiRevisionBehavior`

### Why we need it
Wiki pages need revision history on every meaningful save. That includes:
- incrementing the per-page revision number
- copying the current page content into a revision row
- tracking the editing user and timestamp

That is a side-effect-heavy workflow. Keeping it separate will stop `WikiPagesTable` from turning into a mixed validation plus revision-service class.

### Models that would use it
- `WikiPagesTable`

### Tables it would write to
- `WikiPages`
- `WikiPageRevisions`

## 4. `WikiLinkSyncBehavior`

### Why we need it
Wiki content contains internal links like `[[Page Title]]`. Those links should be parsed and normalized into `wiki_page_links` so the app can build backlinks, trees, and broken-link checks.

This is distinct from revisioning and distinct from validation. It is content parsing plus synchronization work, which is exactly the kind of logic that belongs in a behavior.

### Models that would use it
- `WikiPagesTable`

### Tables it would read or write
- `WikiPages`
- `WikiPageLinks`

## 5. `LastAdminProtectionBehavior`

### Why we need it
Projects should never be left without an admin. The protection needs to run on both update and delete paths:
- block demoting the last admin
- block deleting the last admin

The current logic lives naturally on `ProjectMembersTable`, but wrapping it in a behavior makes the policy explicit and reusable if we later add another membership table with admin semantics.

### Models that would use it
- `ProjectMembersTable`

### Tables it would read
- `ProjectMembers`

## 6. `ActivityLogBehavior`

### Why we need it
This is the clearest cross-cutting behavior. Several models should record mutations into `activity_log` with a shared payload shape:
- action name
- actor
- subject type and subject id
- old value
- new value

If each table logs activity differently, the audit trail will drift quickly. A behavior gives us one place to normalize event names and payload formatting.

### Models that would use it
- `IssuesTable`
- `CommentsTable`
- `WikiPagesTable`
- `ProjectMembersTable`
- `AttachmentsTable`
- `IssueRelationsTable`
- `AttachmentLinksTable`

### Tables it would write to
- `ActivityLog`

## Recommended Split

Use table classes for:
- associations
- field validation
- small `buildRules()` integrity checks

Use behaviors for:
- multi-table side effects
- save/delete lifecycle policy
- shared audit logging
- content synchronization

## Recommended Build Order

1. `ProjectSeedingBehavior`
2. `IssueLifecycleBehavior`
3. `WikiRevisionBehavior`
4. `WikiLinkSyncBehavior`
5. `LastAdminProtectionBehavior`
6. `ActivityLogBehavior`

This order keeps project bootstrap and issue workflows stable first, then adds wiki lifecycle support, then centralizes audit logging once the mutation rules are clearer.
