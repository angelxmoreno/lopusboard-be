# What's Next

The backend foundation is now in a stable place:
- validation and integrity rules are in place
- domain behaviors are wired into the relevant tables
- activity logging is implemented for the current model mutations
- model tests are no longer relying on incomplete placeholders
- remote authentication is wired through IdentityBridge and Appwrite
- the API layer has thin Crud-backed `Issues` and `Projects` controllers
- the authenticated identity endpoint is working at `/api/identity/me`

The next work should move up to the API layer in this order.

## 1. Authorization Setup

Add the application authorization layer before rolling out more endpoints:
- choose CakePHP Authorization as the app-level authorization mechanism
- wire the authenticated local user into the authorization identity flow
- define the first policies around project membership and project roles
- decide the standard `401` vs `403` response behavior for API requests

Why first:
- authentication is already done, but authorization is still missing
- project, issue, wiki, and attachment endpoints all depend on project-scoped access rules
- adding authorization after building every controller would create rework

## 2. Project Bootstrap Endpoints

Build the first project-level API surface:
- projects
- project members
- statuses
- departments

Why first:
- `ProjectSeedingBehavior` is already in place
- project creation now has the expected side effects
- other feature areas depend on this project bootstrap flow

## 3. Issue Workflow Endpoints

Build the core board workflow:
- create, update, list, and detail issue endpoints
- kanban reads
- move and reorder actions
- comments
- issue relations

Why next:
- the issue model rules and lifecycle behavior are already enforced
- this is the main product workflow and depends on the project bootstrap layer

## 4. Wiki Endpoints

Build the wiki surface:
- page CRUD
- tree reads
- revision history
- restore revision flow

Why after issues:
- wiki revisioning and link syncing are already implemented in the model layer
- this is a self-contained feature area once project membership and routing are in place

## 5. Attachments And Activity Feed

Finish the supporting collaboration features:
- attachment registration and linking
- project-wide activity endpoint

Why last:
- attachments and audit feed rely on the earlier project, issue, and wiki workflows
- the model-level audit logging is ready, so the API layer can expose it cleanly
