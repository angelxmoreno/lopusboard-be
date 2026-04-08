# What's Next

The backend foundation is now in a stable place:
- validation and integrity rules are in place
- domain behaviors are wired into the relevant tables
- activity logging is implemented for the current model mutations
- model tests are no longer relying on incomplete placeholders
- remote authentication is wired through IdentityBridge and Appwrite
- the API layer has thin Crud-backed `Issues` and `Projects` controllers
- the authenticated identity endpoint is working at `/api/identity/me`
- project authorization helpers, policies, and authorized Crud actions now exist for the `Projects` slice

The next work should keep expanding the API layer in dependency order, with controllers and policies created together.

## 1. Finish The Project-Scoped Setup Slice

Create the next project-scoped controllers and policies:
- `ProjectMembersController`
- `StatusesController`
- `DepartmentsController`
- `ProjectMembersTablePolicy` and `ProjectMemberPolicy`
- `StatusesTablePolicy` and `StatusPolicy`
- `DepartmentsTablePolicy` and `DepartmentPolicy`

Why first:
- almost every other resource depends on project membership and project roles
- this completes the project bootstrap surface around the `Projects` slice that is already wired
- it lets the app establish a stable policy pattern before moving into issues and wiki

## 2. Build The Issue Workflow Slice

Create the issue controllers and policies together:
- `IssuesController`
- `CommentsController`
- `IssueRelationsController`
- `IssuesTablePolicy` and `IssuePolicy`
- `CommentsTablePolicy` and `CommentPolicy`
- `IssueRelationsTablePolicy` and `IssueRelationPolicy`

Why next:
- issue access rules build directly on project membership
- this is the main product workflow
- it reuses the authorized Crud action pattern you already have

## 3. Build The Wiki Slice

Create the wiki controllers and policies together:
- `WikiPagesController`
- `WikiPageRevisionsController`
- `WikiPagesTablePolicy` and `WikiPagePolicy`
- `WikiPageRevisionsTablePolicy` and `WikiPageRevisionPolicy`

Why after issues:
- wiki still depends on the same project-scoped authorization rules
- by this point the project and issue patterns should be settled enough to copy cleanly

## 4. Finish Attachments And Activity Feed

Create the final supporting controllers and policies:
- `AttachmentsController`
- `ActivityLogController` as read-only
- `AttachmentsTablePolicy` and `AttachmentPolicy`
- `ActivityLogTablePolicy`

Why last:
- attachments and audit feed rely on the earlier project, issue, and wiki workflows
- the model-level audit logging is ready, so the API layer can expose it cleanly

## 5. Delivery Pattern For Each Batch

For each batch above:
- create the controllers
- create the matching policies
- wire only the controllers whose policies are actually implemented
- add integration tests for one or two representative endpoints before moving to the next batch

This keeps velocity high without creating a large set of half-wired controllers and policies.
