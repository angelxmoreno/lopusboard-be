# What's Next

The CRUD and policy rollout is now in place for:
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
- the read-only activity feed

The next phase should focus on client ergonomics and workflow-specific endpoints.

## 1. Create The Node HTTP Client For The Backend

Create a small Node client package or module that wraps the current API surface:
- authentication header handling
- identity endpoint access
- shared request/response typing
- project, issue, wiki, attachment, and activity-feed calls

Why first:
- the backend surface is now broad enough to benefit from one typed client
- frontend work will move faster once the API contract is centralized

## 2. Add Issue Move/Reorder Endpoints

Add custom workflow endpoints for issue movement:
- kanban move
- sibling reorder
- any position-updating actions that should not be expressed as raw CRUD edits

Why next:
- issue workflow is the highest-value custom behavior left
- these actions need intentional authorization and tests

## 3. Add Wiki Revision Restore

Add the wiki restore flow as an explicit endpoint:
- choose a revision
- restore its body into the live wiki page
- ensure revision and activity logging behavior still works

Why next:
- the revisions model and read endpoints already exist
- restore is the main missing wiki workflow action

## 4. Expand Authorization Integration Tests

Add more endpoint-level authorization coverage:
- member vs admin edit/delete differences
- cross-project denial cases
- custom workflow action checks once they exist

Why after the new endpoints:
- the basic CRUD authorization is already covered
- the higher-risk gaps are now in workflow actions and role boundaries
