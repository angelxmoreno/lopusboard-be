# LopusBoard — Backend API

> *The headless REST API powering the LopusBoard workspace.*

LopusBoard Backend is a robust, project-isolated API-first application built with CakePHP 5. It serves as the single source of truth for all project data, including issues, wiki pages, team memberships, and activity logs.

---

## Overview

The LopusBoard backend is designed as a **pure REST API**. It does not serve HTML or manage CSS/styling. It is responsible for data integrity, business logic enforcement (e.g., nesting limits, project isolation), and secure identity verification via Appwrite.

### Key Responsibilities
- **JWT Verification:** Custom middleware verifies Appwrite JWTs on every request via the Appwrite PHP SDK.
- **User Sync:** Automatically synchronizes local `users` records from Appwrite identity data on first login.
- **Project Isolation:** Enforces strict boundaries between projects; users can only access data within projects they are members of.
- **Wiki Revisioning:** Automatically snapshots wiki page bodies on every save to provide version history.
- **Activity Logging:** Records field-level diffs for every mutation to drive the project-wide audit trail.
- **Headless Architecture:** Provides a standardized JSON interface for any client (React frontend, mobile apps, or AI agents).

---

## Tech Stack

| Layer | Technology |
|---|---|
| **Framework** | [CakePHP 5](https://cakephp.org/) |
| **Language** | [PHP 8.5.4](https://www.php.net/) |
| **Database** | [MySQL 8](https://www.mysql.com/) |
| **Auth Verification** | [Appwrite PHP SDK](https://appwrite.io/docs/sdks#php) |
| **Dependency Management** | [Composer](https://getcomposer.org/) |
| **Standardization** | PSR-12 / Biome |

---

## Getting Started

### 1. Prerequisites
- PHP 8.5.4 or higher
- MySQL 8.0 or higher
- Composer

### 2. Environment Variables
Create a `.env` file in the root of this directory. You can use the `be.env` template provided in the project root:

```bash
cp ../be.env .env
```

Ensure you generate a unique `SECURITY_SALT` as per the CakePHP requirements.

### 3. Installation
```bash
composer install
```

### 4. Database Setup
Run the migrations to set up the 14-table schema (see `wiki/03-schema.md` for details):

```bash
bin/cake migrations migrate
```

Load a rerunnable local demo dataset with one user, one project, and related placeholder records:

```bash
bin/cake migrations seed --seed DemoDataSeed
```

### 5. Running the Server
```bash
bin/cake server -p 8080
```

The API will be available at `http://localhost:8080/api`.

---

## Core Architecture

### Auth Flow
The backend never issues tokens. It expects a `Bearer` token in the `Authorization` header.
- **Middleware:** `src/Middleware/AppwriteAuthMiddleware.php` intercepts requests.
- **Verification:** Calls Appwrite API to confirm token validity.
- **Identity:** Resolves the local `User` entity and attaches it to the request.

### Model Associations
We use CakePHP's ORM with several aliased associations to handle complex relationships like `created_by` and `assignee_id` pointing to the same `Users` table. See `wiki/09-cake-models.md` for the association map.

### Domain Enums And Validation
Allowed-value fields are centralized as backed enums under `src/Model/Enum`. This is the source of truth for model-layer value sets such as:
- issue `type`
- issue `priority`
- project member `role`
- attachment `storage_provider`
- attachment link `linkable_type`
- activity log `subject_type` and `action`

Tables should not inline repeated `inList()` arrays for these fields. Instead, use the shared helper in `src/Model/Table/AppTable.php`:

```php
$this->addEnumValidation($validator, 'priority', IssuePriority::class);
```

This keeps validators, tests, and future refactors aligned. When adding a new allowed-value field:
1. create or extend an enum in `src/Model/Enum`
2. reference that enum from table validation
3. keep migration and `tests/schema.sql` literals in sync with the enum values

### Model Behaviors
The remaining model-layer business logic is being organized around a small behavior set rather than large table callbacks. The proposed behavior map is documented in [project-files/proposed-behaviors.md](project-files/proposed-behaviors.md).

Current behavior targets:
- `ProjectSeedingBehavior`
- `IssueLifecycleBehavior`
- `WikiRevisionBehavior`
- `WikiLinkSyncBehavior`
- `LastAdminProtectionBehavior`
- `ActivityLogBehavior`

Table classes should continue to own associations, field validation, and small integrity checks. Cross-cutting side effects and save/delete lifecycle rules should live in behaviors.
`ActivityLogBehavior` is also wired for relation and attachment-link mutations so future saves and deletes on those join tables can reach the project audit feed without bespoke controller logging.

---

## API Endpoints
All endpoints return JSON and follow RESTful conventions.
- `GET /api/projects` — List user's projects
- `POST /api/projects/:id/issues` — Create a new issue
- `GET /api/health` — Public health check

For a full list of endpoints and request/response shapes, refer to [08-api-endpoints.md](../wiki/08-api-endpoints.md).

---

## Scripts & Tooling

| Command | Description |
|---|---|
| `bin/cake bake all <Table>` | Generate Model, Controller, and Templates |
| `bin/cake migrations migrate` | Run database migrations |
| `bin/cake migrations seed --seed DemoDataSeed` | Seed one demo user plus placeholder project data |
| `vendor/bin/phpunit` | Run the test suite |
| `vendor/bin/phpstan` | Static analysis (Level 8) |

---

## Quality Tooling

We maintain high code quality through a suite of automated checks.

### Local Git Hooks (CaptainHook)
Local git hooks are installed to ensure every commit and push meets our standards:
- **`pre-commit`**: Runs `composer cs-check` (Coding Standards).
- **`commit-msg`**: Validates that commit messages follow [Conventional Commits](https://www.conventionalcommits.org/).
- **`pre-push`**: Runs `composer check` (Tests + CS + Static Analysis) and `composer md` (Maintainability).

### Manual Commands
| Command | Description |
|---|---|
| `composer cs-check` | Check coding standards (PHP_CodeSniffer) |
| `composer cs-fix` | Automatically fix coding standard violations |
| `composer stan` | Run PHPStan static analysis |
| `composer md` | Run PHPMD maintainability checks |
| `composer check` | Run all mandatory quality gates (Tests, CS, Stan) |

---

## Documentation Links
- [LopusBoard Project Wiki](../wiki/README.md)
- [CakePHP Documentation](https://book.cakephp.org/5/en/index.html)
- [Appwrite SDK Reference](https://appwrite.io/docs/references/cloud/server-php/account)
