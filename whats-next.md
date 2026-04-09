# What's Next

The API rollout that was planned here is now complete:
- project-scoped setup controllers and policies are in place
- issue workflow controllers and policies are in place
- wiki controllers and policies are in place
- attachments and the read-only activity feed are in place
- the shared authorized Crud action pattern is wired across the current API surface

The next work should build on that completed foundation instead of adding more basic CRUD endpoints.

## 1. Apply Policies To Non-CRUD Actions

Add authorization checks for actions that do not map cleanly to the standard REST set:
- issue move and reorder actions
- wiki restore revision flow
- any project membership or status transitions with custom behavior

Why next:
- the main CRUD surface is now protected
- the remaining risk is custom workflow actions falling outside the current policy wiring

## 2. Add Query Scoping And Policy Coverage For Remaining Read Models

Tighten any read paths that still need explicit scoping or dedicated policies:
- attachment links
- wiki page links and backlinks
- dashboard-style aggregated reads

Why next:
- these are the most likely places for accidental cross-project data leakage
- the project authorization helper is already ready to support them

## 3. Expand Integration Tests For Authorization Behavior

Add more endpoint-level tests that prove role differences, not just happy-path access:
- member vs admin delete/edit behavior
- cross-project denial cases
- read-only controller restrictions

Why next:
- the policy layer is now broad enough that regressions are more likely to come from integration wiring than missing files

## 4. Add Custom Workflow Endpoints Intentionally

Once the policy layer is stable, add the next purpose-built API actions rather than more raw CRUD:
- issue kanban move/reorder
- wiki revision restore
- attachment linking flows if they belong in the API

Why after the policy/test pass:
- those actions encode business rules directly
- they should land on top of a proven authorization baseline
