# Repository Guidelines

## Project Structure & Module Organization
This repository is a CakePHP 5 backend. Application bootstrap and middleware live in `src/Application.php`; shared controller behavior starts in `src/Controller/AppController.php`. Domain code should go under `src/Model/Table`, `src/Model/Entity`, and `src/Model/Behavior`. Routes are defined in `config/routes.php`, and environment/bootstrap settings live in `config/`. Tests live in `tests/TestCase` with fixtures or schema support in `tests/Fixture` and `tests/schema.sql`. `templates/` and `webroot/` still contain the CakePHP skeleton UI assets, even though the README positions the project as API-first.

## Build, Test, and Development Commands
Use Composer scripts where available:

- `composer install`: install PHP dependencies and run post-install setup.
- `composer test`: run the PHPUnit suite.
- `composer cs-check`: run CakePHP CodeSniffer checks.
- `composer cs-fix`: auto-fix CodeSniffer issues where possible.
- `composer check`: run tests plus coding-standard checks.
- `vendor/bin/phpstan`: run static analysis at level 8.
- `bin/cake migrations migrate`: apply database migrations.
- `bin/cake server -p 8080`: start the local dev server.

Run a single test file with `vendor/bin/phpunit tests/TestCase/ApplicationTest.php`.

## Coding Style & Naming Conventions
Follow PSR-12 conventions as enforced by `cakephp/cakephp-codesniffer`. Use 4-space indentation, LF line endings, and a final newline; YAML files use 2 spaces, and `.neon` files use tabs per `.editorconfig`. Keep `declare(strict_types=1);` at the top of PHP files. Match CakePHP naming: `UsersTable.php`, `User.php`, `UsersController.php`, and dashed routes via `DashedRoute`.

## Testing Guidelines
PHPUnit is configured through `phpunit.xml.dist`; test bootstrap runs migrations before the suite, so keep migrations healthy when changing schema. Add or update tests in the mirrored `tests/TestCase/...` namespace for every behavior change. CI runs PHPUnit on PHP 8.2 and 8.5, plus PHP CodeSniffer and PHPStan.

## Commit & Pull Request Guidelines
Recent history uses Conventional Commit prefixes (`feat:`, `chore:`); keep that format and make scopes descriptive when useful. Pull requests should summarize the big picture, link the related issue when applicable, and include tests for new behavior or bug fixes. The existing PR template also expects contributors to verify the test suite still passes before requesting review.
