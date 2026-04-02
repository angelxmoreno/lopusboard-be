# Validation And Integrity Review

This explains what “validation and integrity rules are too weak” means in the current baked models and how to fix it in CakePHP.

## The distinction

CakePHP gives you two main guardrails:

- `validationDefault()`
  Use for request-shape validation.
  Examples: required fields, enum values, regex patterns, email format, URL format.

- `buildRules()`
  Use for application integrity checks.
  Examples: `existsIn()`, uniqueness, cross-table or scoped business checks.

Right now most tables only do the minimum baked checks. That is not enough for the API contract in `project-files/API_SPECIFICATION.md`, which expects invalid input to fail cleanly with `422`, not with late SQL errors or inconsistent saved data.

## Concrete gaps

### ProjectsTable
Current gap:
- `created_by` is validated as an integer, but not checked with `existsIn(['created_by'], 'Creators')`

Fix:
- add `belongsTo('Creators', ...)`
- add `existsIn(['created_by'], 'Creators')`

### StatusesTable
Current gaps:
- `color` only checks string length
- `position` is decimal but not constrained to positive values

Fix:
- enforce hex format with regex: `^#[0-9A-Fa-f]{6}$`
- add a custom rule that `position > 0`

### IssuesTable
Current gaps:
- `type` is not constrained to `issue|task`
- `priority` is not constrained to `low|medium|high|critical`
- `created_by` is not checked against users
- `status_id` is only checked for existence, not same-project scope
- no rule ensures `parent_id` belongs to the same project
- no rule prevents task-on-task nesting

Fix:
- `inList()` for `type` and `priority`
- `existsIn(['created_by'], 'Creators')`
- custom `buildRules()` methods for same-project checks
- custom save rule for one-level nesting

### WikiPagesTable
Current gaps:
- `slug` is required from input even though the spec says it should be generated
- `created_by` and `last_edited_by` are not validated against users
- no same-project check on parent page

Fix:
- generate slug before validation/save
- add `Creators` and `LastEditors` associations
- add `existsIn()` plus a scoped rule ensuring parent page belongs to the same project

### WikiPageRevisionsTable
Current gap:
- `edited_by` is not checked against users

Fix:
- add `belongsTo('Editors', ...)`
- add `existsIn(['edited_by'], 'Editors')`

### AttachmentsTable
Current gaps:
- `storage_provider` is unconstrained
- `uploaded_by` is not checked against users
- `external_url` is not validated as a URL

Fix:
- `inList(['google_drive', 'minio'])`
- `existsIn(['uploaded_by'], 'Uploaders')`
- `url('external_url')` if present

### AttachmentLinksTable
Current gaps:
- `linkable_type` is unconstrained
- `linkable_id` is not verified against the selected target table

Fix:
- `inList(['issue', 'wiki_page'])`
- custom rule that resolves the correct table from `linkable_type` and checks existence of `linkable_id`

### ActivityLogTable
Current gaps:
- `subject_type` is unconstrained
- `action` is unconstrained
- `old_value` and `new_value` are not validated as structured payloads

Fix:
- `inList()` for allowed subject types and actions
- cast/store JSON consistently
- keep writes internal to the application, not client-driven

### ProjectMembersTable
Current gaps:
- `role` is unconstrained
- no last-admin protection

Fix:
- `inList(['admin', 'member'])`
- custom rule / callback to prevent deleting or demoting the last admin

## Recommended repair order

1. Add missing associations needed by `existsIn()`
2. Tighten `validationDefault()` for enums, regexes, URLs, and required fields
3. Tighten `buildRules()` for foreign keys and scoped integrity
4. Add custom callbacks/rules for cross-row business logic
5. Add model tests that assert validation errors instead of DB exceptions
