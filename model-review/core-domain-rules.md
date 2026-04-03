# Core Domain Rules

This explains what “core domain rules are not implemented in the model layer” means for this repo and how to address it in CakePHP.

Implementation status:
- the behavior split described below is now implemented under `src/Model/Behavior`
- table classes still own validation, associations, and small integrity rules
- side effects and lifecycle policy now live in dedicated behaviors
- activity logging now also covers issue relation and attachment-link mutations via table-level behavior wiring

## Why the model layer matters

These rules should live in `src/Model/Table` and model behaviors because they protect data integrity regardless of whether writes come from controllers, commands, tests, or future MCP/agent entrypoints. Right now the baked tables mostly validate column shape; they do not enforce product behavior from `project-files/BUSINESS_LOGIC.md`.

## Rules captured in behaviors

### Projects
- On project create, seed default `statuses`
- On project create, seed default `departments`
- On project create, add the creator to `project_members` as `admin`

Current home:
- `ProjectsTable::afterSave()`
- or `ProjectSeedingBehavior`

### Issues and Tasks
- Prevent a task from having subtasks
- Ensure a task inherits its parent issue’s `project_id`
- Prevent changing `project_id` after create
- Auto-append new issues at `MAX(position) + 1`
- Ensure `status_id` belongs to the same project
- Optionally ensure `department_id` and `assignee_id` are valid for the same project context

Current home:
- `IssuesTable::validationDefault()`
- `IssuesTable::buildRules()`
- `IssuesTable::beforeSave()`
- or an `IssueRulesBehavior`

### Wiki Pages
- Generate `slug` from `title` on create
- Keep `slug` unique within project
- Create a revision row on every save
- Parse `[[Page Title]]` links and sync `wiki_page_links`

Current home:
- `WikiPagesTable::beforeMarshal()` or `beforeSave()` for slugging
- `WikiPagesTable::afterSave()` for revisions and link sync
- or separate `WikiSlugBehavior`, `WikiRevisionBehavior`, `WikiLinkSyncBehavior`

### Project Members
- Prevent deleting or demoting the last admin in a project

Current home:
- `ProjectMembersTable::buildRules()`
- `ProjectMembersTable::beforeDelete()`
- `ProjectMembersTable::beforeSave()`
- or `LastAdminProtectionBehavior`

### Activity Log
- Record mutations for issues, wiki pages, comments, members, and attachments
- Store `old_value` and `new_value` as structured JSON

Current home:
- table callbacks on the affected models
- or a reusable `ActivityLogBehavior`

## Suggested behavior split

Use behaviors where the logic is cross-cutting or side-effect heavy:

- `ProjectSeedingBehavior`
- `IssueLifecycleBehavior`
- `WikiRevisionBehavior`
- `WikiLinkSyncBehavior`
- `LastAdminProtectionBehavior`
- `ActivityLogBehavior`

Keep pure validation and foreign-key integrity in the table class. Move reusable lifecycle logic into behaviors.

## Practical implementation order

1. Lock down entity mass assignment
2. Fix associations and finder names
3. Add validator and `buildRules()` checks
4. Add behaviors and callbacks for side effects
5. Add model tests for each rule before building controllers on top
