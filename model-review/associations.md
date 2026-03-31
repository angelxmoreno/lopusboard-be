# Missing Associations And Finders

This file lists the gaps between the baked models in `src/Model` and the required model contract in `project-files/MODEL_DEFINITIONS.md`.

## UsersTable

Current file: `src/Model/Table/UsersTable.php`

Missing associations:
- `hasMany('Projects', ['foreignKey' => 'created_by'])`
- `hasMany('Issues', ['foreignKey' => 'created_by'])`
- `hasMany('AssignedIssues', ['className' => 'Issues', 'foreignKey' => 'assignee_id'])`
- `hasMany('WikiPages', ['foreignKey' => 'created_by'])`
- `hasMany('ActivityLog', ['foreignKey' => 'actor_id'])`

Already present:
- `Comments`
- `ProjectMembers`

## ProjectsTable

Current file: `src/Model/Table/ProjectsTable.php`

Missing associations:
- `belongsTo('Creators', ['className' => 'Users', 'foreignKey' => 'created_by'])`

Notes:
- The spec does not call this one out explicitly, but it is needed to model `created_by` cleanly and to support project ownership semantics.

## IssuesTable

Current file: `src/Model/Table/IssuesTable.php`

Missing associations from the spec:
- `belongsTo('Creators', ['className' => 'Users', 'foreignKey' => 'created_by'])`
- `hasMany('Tasks', ['className' => 'Issues', 'foreignKey' => 'parent_id'])`
- `hasMany('ActivityLog', ['foreignKey' => 'subject_id', 'conditions' => ['ActivityLog.subject_type IN' => ['issue', 'task']]])`

Present but misnamed relative to the spec:
- `ChildIssues` should likely be `Tasks`

Missing finder:
- `findForKanban(Query $query, array $options): Query`

Expected behavior of `findForKanban()`:
- scope to `project_id`
- filter to `type = issue`
- contain `Statuses`, `Assignees`, `Departments`
- order by `status_id`, `position`

## WikiPagesTable

Current file: `src/Model/Table/WikiPagesTable.php`

Missing associations from the spec:
- `belongsTo('Creators', ['className' => 'Users', 'foreignKey' => 'created_by'])`
- `belongsTo('LastEditors', ['className' => 'Users', 'foreignKey' => 'last_edited_by'])`
- `hasMany('WikiPageLinks', ['foreignKey' => 'source_page_id'])`

Present but misnamed relative to the spec:
- `ParentWikiPages` should likely be `ParentPages`
- `ChildWikiPages` should likely be `ChildPages`

Missing finder:
- `findTree(Query $query, array $options): Query`

Expected behavior of `findTree()`:
- scope to `project_id`
- order by `position`

## WikiPageRevisionsTable

Current file: `src/Model/Table/WikiPageRevisionsTable.php`

Missing associations:
- `belongsTo('Editors', ['className' => 'Users', 'foreignKey' => 'edited_by'])`

## AttachmentsTable

Current file: `src/Model/Table/AttachmentsTable.php`

Missing associations:
- `belongsTo('Uploaders', ['className' => 'Users', 'foreignKey' => 'uploaded_by'])`

## AttachmentLinksTable

Current file: `src/Model/Table/AttachmentLinksTable.php`

Missing associations:
- no typed relation helpers for the polymorphic target

Notes:
- Cake cannot enforce the `linkable_type` polymorphic target automatically with a simple baked association.
- You will likely need custom validation/rules instead of conventional associations for the link target.

## ActivityLogTable

Current file: `src/Model/Table/ActivityLogTable.php`

Missing associations:
- no typed helpers for `subject_type` / `subject_id`

Notes:
- Like `AttachmentLinksTable`, this is polymorphic. Standard Cake associations are not enough by themselves.

## Summary

The biggest structural gaps are in:
- `UsersTable`
- `IssuesTable`
- `WikiPagesTable`

Those three tables need association cleanup first, because later business rules and controller queries will depend on the alias names being stable.
