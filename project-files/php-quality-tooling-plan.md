# PHP Quality Tooling Plan

This is the execution plan to give Gemini CLI so it can install and configure the PHP equivalent of:

- Lefthook
- Biome
- Conventional Commits enforcement
- duplicate detection

For this CakePHP repo, the target stack is:

- `captainhook/captainhook` for git hooks
- existing `phpcs` for coding standards
- existing `phpunit` for tests
- `phpstan/phpstan` for static analysis
- `phpmd/phpmd` for maintainability checks
- `sebastian/phpcpd` for duplicate detection
- a small PHP `commit-msg` validator script for Conventional Commits

Do not invent alternatives unless blocked. Follow this plan as written.

## Goals

- keep all quality gates in PHP/Composer tooling
- avoid introducing Node-based hook tooling into this repo
- make local hooks align with CI checks
- keep the commit message validation simple and repo-owned
- preserve the existing `composer check` workflow while expanding it

## Existing State

- `phpcs` is already installed and configured via `phpcs.xml`
- `phpunit` is already installed
- `composer.json` already has:
  - `cs-check`
  - `cs-fix`
  - `test`
  - `check`
- `phpstan.neon` exists, but `phpstan/phpstan` is not currently installed as a dev dependency
- no CaptainHook config exists
- no PHPMD config exists
- no PHPCPD config exists
- no Conventional Commit hook exists

## Packages To Add

Install these dev dependencies:

```bash
composer require --dev \
  captainhook/captainhook \
  phpstan/phpstan \
  phpmd/phpmd \
  sebastian/phpcpd
```

Do not remove any existing packages.

## Files To Create Or Update

### 1. Update `composer.json`

Add or update scripts so the repo has these commands:

- `stan`
  - runs `phpstan analyse`
- `md`
  - runs `phpmd src,tests,plugins ansi phpmd.xml`
- `cpd`
  - runs `phpcpd src plugins`
- `quality`
  - runs `@cs-check`, `@stan`, `@md`
- `check`
  - keep tests and coding standards
  - expand it to also include `@stan`

Do not add `cpd` to the default `check` script yet. Duplicate detection is useful, but it is noisy and should not block the first rollout unless the repo is already clean.

### 2. Create `phpmd.xml`

Create a repo-level PHPMD config file.

Start with these rulesets:

- `cleancode`
- `codesize`
- `design`
- `naming`
- `unusedcode`

Exclude:

- `vendor`
- `tmp`
- `logs`
- `templates`
- `webroot`

If PHPMD is too noisy on generated or framework-heavy files, add targeted excludes for:

- `src/Controller`
- `tests`

But do that only if the first run is unreasonably noisy.

### 3. Create `.phpcpd.xml.dist`

Create a minimal PHPCPD config file.

Target:

- `src`
- `plugins`

Exclude:

- `vendor`
- `tests`
- `tmp`
- `logs`
- `templates`
- `webroot`

Use a conservative threshold so it catches real copy/paste, not normal Cake boilerplate.

### 4. Create `captainhook.json`

Create a CaptainHook config in the repo root.

Hook setup:

#### `pre-commit`

Run:

- `composer cs-check`

Do not run the full test suite on every commit yet.

#### `commit-msg`

Run a PHP script that validates Conventional Commits.

#### `pre-push`

Run:

- `composer push-check`

Do not run `composer cpd` in `pre-push` yet unless the repo is already clean.

### 5. Create `bin/validate-commit-message.php`

Create a small PHP CLI script that validates commit messages against this pattern:

- `feat: ...`
- `fix: ...`
- `refactor: ...`
- `test: ...`
- `docs: ...`
- `chore: ...`
- optional scope allowed

Accepted examples:

- `feat: added projects authorization policies`
- `fix(auth): corrected appwrite jwt flow`
- `chore(ci): added captainhook and phpstan`

Rejected examples:

- `updated stuff`
- `fix auth`
- `WIP`

The script should:

- read the commit message file path from argv
- ignore comment lines starting with `#`
- validate only the first non-comment line
- print a clear error when invalid
- exit `0` for valid, nonzero for invalid

Use a simple regex. Do not overengineer this.

### 6. Update `.github/workflows/ci.yml`

Keep the current jobs, but expand the coding-standard/static-analysis job so CI also runs:

- `composer md`

Optionally add:

- `composer stan`

if not already effectively covered

Do not add `phpcpd` to CI in the first pass unless the repo is already clean.

### 7. Update `README.md`

Add a short section describing the local quality tooling:

- CaptainHook is installed for local hooks
- `composer cs-check`
- `composer test`
- `composer stan`
- `composer md`
- `composer check`

Also mention that commit messages are validated as Conventional Commits.

## Hook Behavior To Aim For

### On Commit

The developer should get:

- fast style feedback
- commit message validation

That means:

- `pre-commit` should stay fast
- `commit-msg` should be instant

### On Push

The developer should get:

- test coverage
- style checks
- static analysis
- PHPMD maintainability checks

That means `pre-push` can be slower.

## Acceptance Criteria

Gemini should consider the task done only if all of these are true:

1. `composer install` installs the new tooling cleanly
2. `composer cs-check` still works
3. `composer stan` works
4. `composer md` works
5. `composer check` works
6. CaptainHook installs hooks successfully
7. an invalid commit message is rejected
8. a valid Conventional Commit message is accepted
9. the README documents the new workflow
10. CI is updated to run the new maintainability check

## Implementation Notes

- Prefer repo-owned PHP scripts over introducing Node-based commit tooling
- Do not replace `phpcs`; extend around it
- Keep the first rollout conservative so the repo does not become unusable from noisy hooks
- If PHPMD or PHPCPD produce too much noise, configure them, do not silently remove them

## Suggested Commit Breakdown

### Commit 1

`chore: added php quality tooling dependencies and scripts`

Includes:

- Composer dev dependencies
- `composer.json` script updates
- `phpmd.xml`
- `.phpcpd.xml.dist`

### Commit 2

`chore: added captainhook and conventional commit validation`

Includes:

- `captainhook.json`
- `bin/validate-commit-message.php`
- hook installation wiring

### Commit 3

`chore: aligned ci and docs with local quality tooling`

Includes:

- `.github/workflows/ci.yml`
- `README.md`

## Final Instruction To Gemini

Implement the plan in the order above. After each commit-sized step:

1. run the relevant Composer commands
2. fix any breakage before moving on
3. keep changes minimal and idiomatic for a CakePHP repo

Do not add unrelated refactors while doing this work.
