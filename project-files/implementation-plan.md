# Implementation Plan

The repository is still close to the CakePHP starter app, while the `project-files` specs describe a JSON API with auth, project isolation, wiki, attachments, and audit logging. Build it in the order below so infrastructure and schema land before feature endpoints.

## 1. Convert the skeleton app into an API-first backend
**Goal:** Remove the HTML starter assumptions, introduce an `/api` route scope, and add the base controller/error shape every later endpoint will rely on.

**Files to create or modify**
- `composer.json`
- `composer.lock`
- `config/bootstrap.php`
- `config/app.php`
- `config/app_local.example.php`
- `config/routes.php`
- `src/Application.php`
- `src/Controller/AppController.php`
- `src/Controller/Api/AppController.php`
- `src/Controller/Api/HealthController.php`
- `tests/TestCase/ApplicationTest.php`
- `tests/TestCase/Controller/Api/HealthControllerTest.php`
- Remove or retire `src/Controller/PagesController.php`, `templates/Pages/home.php`, and `tests/TestCase/Controller/PagesControllerTest.php`

**Conventional commit**
- `chore: convert cakephp skeleton into api-first backend`

## 2. Add the database schema and generate the ORM baseline
**Goal:** Create the canonical schema from `DATABASE_SCHEMA.sql`, then bake the models/entities/controllers so later commits refine real files instead of inventing structure ad hoc.

**Files to create or modify**
- `config/Migrations/<timestamp>_CreateUsersAndProjects.php`
- `config/Migrations/<timestamp>_CreateLookups.php`
- `config/Migrations/<timestamp>_CreateIssuesAndComments.php`
- `config/Migrations/<timestamp>_CreateWikiTables.php`
- `config/Migrations/<timestamp>_CreateAttachmentsAndActivity.php`
- `src/Model/Table/{Users,Projects,ProjectMembers,Statuses,Departments,Issues,IssueRelations,Comments,WikiPages,WikiPageRevisions,WikiPageLinks,Attachments,AttachmentLinks,ActivityLog}Table.php`
- `src/Model/Entity/{User,Project,ProjectMember,Status,Department,Issue,IssueRelation,Comment,WikiPage,WikiPageRevision,WikiPageLink,Attachment,AttachmentLink,ActivityLog}.php`
- `tests/TestCase/Model/Table/{Users,Projects,ProjectMembers,Statuses,Departments,Issues,WikiPages,Attachments,ActivityLog}TableTest.php`

**Conventional commit**
- `feat: add core schema migrations and baked domain models`

## 3. Implement Appwrite auth, identity sync, and project access checks
**Goal:** Verify JWTs, sync users into `users`, attach the identity to the request, and centralize project membership/admin checks before resource endpoints start depending on them.

**Files to create or modify**
- `src/Middleware/AppwriteAuthMiddleware.php`
- `src/Application.php`
- `src/Controller/Api/AppController.php`
- `src/Controller/Api/MeController.php`
- `src/Model/Table/UsersTable.php`
- `src/Service/AppwriteIdentityService.php`
- `src/Service/ProjectAccessService.php`
- `tests/TestCase/Middleware/AppwriteAuthMiddlewareTest.php`
- `tests/TestCase/Controller/Api/MeControllerTest.php`
- `tests/TestCase/Model/Table/UsersTableTest.php`

**Conventional commit**
- `feat: add appwrite authentication and user sync`

## 4. Build projects, members, statuses, and departments
**Goal:** Deliver the first usable project workflow: create projects, seed default lookups, add the creator as admin, and expose member/status/department endpoints.

**Files to create or modify**
- `src/Controller/Api/ProjectsController.php`
- `src/Controller/Api/ProjectMembersController.php`
- `src/Controller/Api/StatusesController.php`
- `src/Controller/Api/DepartmentsController.php`
- `src/Model/Table/ProjectsTable.php`
- `src/Model/Table/ProjectMembersTable.php`
- `src/Model/Table/StatusesTable.php`
- `src/Model/Table/DepartmentsTable.php`
- `tests/TestCase/Controller/Api/{ProjectsController,ProjectMembersController,StatusesController,DepartmentsController}Test.php`
- `tests/TestCase/Model/Table/{ProjectsTable,ProjectMembersTable,StatusesTable,DepartmentsTable}Test.php`

**Conventional commit**
- `feat: implement projects membership and seeded lookups`

## 5. Implement issues, tasks, comments, and relations
**Goal:** Add the core board workflow with validation for same-project references, one-level task nesting, immutable `project_id`, position handling, and issue detail responses.

**Files to create or modify**
- `src/Controller/Api/IssuesController.php`
- `src/Model/Table/IssuesTable.php`
- `src/Model/Table/IssueRelationsTable.php`
- `src/Model/Table/CommentsTable.php`
- `src/Model/Entity/{Issue,IssueRelation,Comment}.php`
- `tests/TestCase/Controller/Api/IssuesControllerTest.php`
- `tests/TestCase/Model/Table/{IssuesTable,IssueRelationsTable,CommentsTable}Test.php`

**Conventional commit**
- `feat: implement issue and task workflows`

## 6. Add wiki pages, revisions, and internal-link syncing
**Goal:** Deliver the wiki feature set with tree reads, slug generation, revision history, restore flow, and markdown link parsing into `wiki_page_links`.

**Files to create or modify**
- `src/Controller/Api/WikiPagesController.php`
- `src/Controller/Api/WikiPageRevisionsController.php`
- `src/Model/Table/WikiPagesTable.php`
- `src/Model/Table/WikiPageRevisionsTable.php`
- `src/Model/Table/WikiPageLinksTable.php`
- `src/Model/Behavior/WikiRevisionBehavior.php`
- `src/Model/Behavior/WikiLinkSyncBehavior.php`
- `tests/TestCase/Controller/Api/{WikiPagesController,WikiPageRevisionsController}Test.php`
- `tests/TestCase/Model/Table/{WikiPagesTable,WikiPageRevisionsTable,WikiPageLinksTable}Test.php`

**Conventional commit**
- `feat: implement wiki revisions and page link syncing`

## 7. Add attachments and the project activity feed
**Goal:** Register Google Drive-backed attachments, link them to issues/wiki pages, and persist activity rows for all important mutations and feed reads.

**Files to create or modify**
- `src/Controller/Api/AttachmentsController.php`
- `src/Controller/Api/ActivityLogController.php`
- `src/Model/Table/AttachmentsTable.php`
- `src/Model/Table/AttachmentLinksTable.php`
- `src/Model/Table/ActivityLogTable.php`
- `src/Model/Behavior/ActivityLogBehavior.php`
- `src/Service/AttachmentService.php`
- `tests/TestCase/Controller/Api/{AttachmentsController,ActivityLogController}Test.php`
- `tests/TestCase/Model/Table/{AttachmentsTable,AttachmentLinksTable,ActivityLogTable}Test.php`

**Conventional commit**
- `feat: add attachments and project activity feed`

## 8. Harden validation, CI, and developer docs
**Goal:** Finish the backend by tightening validation/error responses, ensuring CI runs the right checks for the new API, and updating docs so local setup matches the implemented system.

**Files to create or modify**
- `.github/workflows/ci.yml`
- `phpunit.xml.dist`
- `phpstan.neon`
- `README.md`
- `AGENTS.md`
- `project-files/SETUP_GUIDE.md` if the real setup diverges from the draft spec

**Conventional commit**
- `chore: harden ci validation and backend documentation`

## Suggested validation after each section
- `composer test`
- `composer cs-check`
- `vendor/bin/phpstan`
- `bin/cake migrations migrate`
- Smoke-test the new endpoints with `curl` or an API client before moving to the next commit group
