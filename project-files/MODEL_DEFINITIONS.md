# CakePHP Models & Associations

After running `bin/cake bake all`, apply these associations and behaviors to the generated `Table` classes.

## General Behaviors
- **Timestamp:** Add `$this->addBehavior('Timestamp');` to all Table classes to auto-manage `created` and `modified`.

## Key Model Overrides

### UsersTable
```php
$this->hasMany('Projects', ['foreignKey' => 'created_by']);
$this->hasMany('ProjectMembers', ['foreignKey' => 'user_id']);
$this->hasMany('Issues', ['foreignKey' => 'created_by']);
$this->hasMany('AssignedIssues', ['className' => 'Issues', 'foreignKey' => 'assignee_id']);
$this->hasMany('WikiPages', ['foreignKey' => 'created_by']);
$this->hasMany('ActivityLog', ['foreignKey' => 'actor_id']);
```

### IssuesTable (Self-Referencing & Aliases)
```php
$this->belongsTo('Projects');
$this->belongsTo('Statuses');
$this->belongsTo('Departments');
$this->belongsTo('Assignees', ['className' => 'Users', 'foreignKey' => 'assignee_id']);
$this->belongsTo('Creators', ['className' => 'Users', 'foreignKey' => 'created_by']);
$this->belongsTo('ParentIssues', ['className' => 'Issues', 'foreignKey' => 'parent_id']);
$this->hasMany('Tasks', ['className' => 'Issues', 'foreignKey' => 'parent_id']);
$this->hasMany('Comments');
$this->hasMany('IssueRelations');
$this->hasMany('ActivityLog', [
    'foreignKey' => 'subject_id',
    'conditions' => ['ActivityLog.subject_type IN' => ['issue', 'task']],
]);
```

### WikiPagesTable (Self-Referencing & Aliases)
```php
$this->belongsTo('Projects');
$this->belongsTo('ParentPages', ['className' => 'WikiPages', 'foreignKey' => 'parent_id']);
$this->hasMany('ChildPages', ['className' => 'WikiPages', 'foreignKey' => 'parent_id']);
$this->belongsTo('Creators', ['className' => 'Users', 'foreignKey' => 'created_by']);
$this->belongsTo('LastEditors', ['className' => 'Users', 'foreignKey' => 'last_edited_by']);
$this->hasMany('WikiPageRevisions');
```

## Custom Finders

### IssuesTable
```php
public function findForKanban(Query $query, array $options): Query {
    return $query->where(['Issues.project_id' => $options['project_id'], 'Issues.type' => 'issue'])
                 ->contain(['Statuses', 'Assignees', 'Departments'])
                 ->orderBy(['Issues.status_id', 'Issues.position']);
}
```

### WikiPagesTable
```php
public function findTree(Query $query, array $options): Query {
    return $query->where(['WikiPages.project_id' => $options['project_id']])
                 ->orderBy(['WikiPages.position']);
}
```
