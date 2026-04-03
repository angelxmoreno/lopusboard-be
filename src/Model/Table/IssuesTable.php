<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Enum\ActivityAction;
use App\Model\Enum\ActivitySubjectType;
use App\Model\Enum\IssuePriority;
use App\Model\Enum\IssueType;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * Issues Model
 *
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\BelongsTo $Projects
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Creators
 * @property \App\Model\Table\IssuesTable&\Cake\ORM\Association\BelongsTo $ParentIssues
 * @property \App\Model\Table\StatusesTable&\Cake\ORM\Association\BelongsTo $Statuses
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Assignees
 * @property \App\Model\Table\DepartmentsTable&\Cake\ORM\Association\BelongsTo $Departments
 * @property \App\Model\Table\ActivityLogTable&\Cake\ORM\Association\HasMany $ActivityLog
 * @property \App\Model\Table\CommentsTable&\Cake\ORM\Association\HasMany $Comments
 * @property \App\Model\Table\IssueRelationsTable&\Cake\ORM\Association\HasMany $IssueRelations
 * @property \App\Model\Table\IssuesTable&\Cake\ORM\Association\HasMany $Tasks
 * @method \App\Model\Entity\Issue newEmptyEntity()
 * @method \App\Model\Entity\Issue newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Issue> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Issue get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Issue findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Issue patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Issue> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Issue|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Issue saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Issue>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Issue>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Issue>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Issue> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Issue>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Issue>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Issue>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Issue> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class IssuesTable extends AppTable
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('issues');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('IssueLifecycle');
        $this->addBehavior('ActivityLog', [
            'subjectType' => static function (EntityInterface $entity): string {
                return $entity->get('type') === IssueType::Task->value
                    ? ActivitySubjectType::Task->value
                    : ActivitySubjectType::Issue->value;
            },
            'actorField' => 'created_by',
            'projectField' => 'project_id',
            'createAction' => ActivityAction::Created->value,
            'updateAction' => ActivityAction::Updated->value,
            'deleteAction' => ActivityAction::Deleted->value,
            'fieldActions' => [
                'status_id' => ActivityAction::StatusChanged->value,
                'priority' => ActivityAction::PriorityChanged->value,
                'position' => ActivityAction::Moved->value,
                'assignee_id' => static fn(
                    mixed $oldValue,
                    mixed $newValue,
                ): string => $newValue === null ? ActivityAction::Unassigned->value : ActivityAction::Assigned->value,
            ],
        ]);

        $this->belongsTo('Projects', [
            'foreignKey' => 'project_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Creators', [
            'className' => 'Users',
            'foreignKey' => 'created_by',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('ParentIssues', [
            'className' => 'Issues',
            'foreignKey' => 'parent_id',
        ]);
        $this->belongsTo('Statuses', [
            'foreignKey' => 'status_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Assignees', [
            'foreignKey' => 'assignee_id',
            'className' => 'Users',
        ]);
        $this->belongsTo('Departments', [
            'foreignKey' => 'department_id',
        ]);
        $this->hasMany('ActivityLog', [
            'foreignKey' => 'subject_id',
            'conditions' => ['ActivityLog.subject_type IN' => ActivitySubjectType::issueTaskValues()],
        ]);
        $this->hasMany('Comments', [
            'foreignKey' => 'issue_id',
        ]);
        $this->hasMany('IssueRelations', [
            'foreignKey' => 'issue_id',
        ]);
        $this->hasMany('Tasks', [
            'className' => 'Issues',
            'foreignKey' => 'parent_id',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->nonNegativeInteger('project_id')
            ->notEmptyString('project_id');

        $validator
            ->nonNegativeInteger('parent_id')
            ->allowEmptyString('parent_id');

        $validator
            ->scalar('type')
            ->notEmptyString('type');
        $this->addEnumValidation($validator, 'type', IssueType::class);

        $validator
            ->scalar('title')
            ->maxLength('title', 500)
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        $validator
            ->scalar('description')
            ->maxLength('description', 4294967295)
            ->allowEmptyString('description');

        $validator
            ->nonNegativeInteger('status_id')
            ->notEmptyString('status_id');

        $validator
            ->scalar('priority')
            ->notEmptyString('priority');
        $this->addEnumValidation($validator, 'priority', IssuePriority::class);

        $validator
            ->nonNegativeInteger('assignee_id')
            ->allowEmptyString('assignee_id');

        $validator
            ->nonNegativeInteger('department_id')
            ->allowEmptyString('department_id');

        $validator
            ->date('due_date')
            ->allowEmptyDate('due_date');

        $validator
            ->decimal('position')
            ->notEmptyString('position');

        $validator
            ->nonNegativeInteger('created_by')
            ->requirePresence('created_by', 'create')
            ->notEmptyString('created_by');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['project_id'], 'Projects'), ['errorField' => 'project_id']);
        $rules->add($rules->existsIn(['created_by'], 'Creators'), ['errorField' => 'created_by']);
        $rules->add($rules->existsIn(['parent_id'], 'ParentIssues'), ['errorField' => 'parent_id']);
        $rules->add($rules->existsIn(['status_id'], 'Statuses'), ['errorField' => 'status_id']);
        $rules->add($rules->existsIn(['assignee_id'], 'Assignees'), ['errorField' => 'assignee_id']);
        $rules->add($rules->existsIn(['department_id'], 'Departments'), ['errorField' => 'department_id']);
        $rules->add([$this, 'statusBelongsToProject'], 'statusBelongsToProject', [
            'errorField' => 'status_id',
            'message' => __('Status must belong to the same project as the issue.'),
        ]);
        $rules->add([$this, 'parentBelongsToProject'], 'parentBelongsToProject', [
            'errorField' => 'parent_id',
            'message' => __('Parent issue must belong to the same project as the issue.'),
        ]);
        $rules->add([$this, 'parentCanAcceptChildren'], 'parentCanAcceptChildren', [
            'errorField' => 'parent_id',
            'message' => __('Tasks cannot have subtasks.'),
        ]);

        return $rules;
    }

    /**
     * Checks that the selected status belongs to the same project as the issue.
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity being validated.
     * @param array<string, mixed> $options Rule options.
     * @return bool
     */
    public function statusBelongsToProject(EntityInterface $entity, array $options): bool
    {
        $statusId = $entity->get('status_id');
        $projectId = $entity->get('project_id');
        if ($statusId === null || $projectId === null) {
            return true;
        }

        $status = $this->Statuses
            ->find()
            ->select(['project_id'])
            ->where(['Statuses.id' => $statusId])
            ->disableHydration()
            ->first();

        return $status !== null && (int)$status['project_id'] === (int)$projectId;
    }

    /**
     * Checks that the selected parent issue belongs to the same project.
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity being validated.
     * @param array<string, mixed> $options Rule options.
     * @return bool
     */
    public function parentBelongsToProject(EntityInterface $entity, array $options): bool
    {
        $parentId = $entity->get('parent_id');
        $projectId = $entity->get('project_id');
        if ($parentId === null || $projectId === null) {
            return true;
        }

        $parentIssue = $this->ParentIssues
            ->find()
            ->select(['project_id'])
            ->where(['ParentIssues.id' => $parentId])
            ->disableHydration()
            ->first();

        return $parentIssue !== null && (int)$parentIssue['project_id'] === (int)$projectId;
    }

    /**
     * Checks that the selected parent issue is not a task.
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity being validated.
     * @param array<string, mixed> $options Rule options.
     * @return bool
     */
    public function parentCanAcceptChildren(EntityInterface $entity, array $options): bool
    {
        $parentId = $entity->get('parent_id');
        if ($parentId === null) {
            return true;
        }

        $parentIssue = $this->ParentIssues
            ->find()
            ->select(['type'])
            ->where(['ParentIssues.id' => $parentId])
            ->disableHydration()
            ->first();

        return $parentIssue === null || $parentIssue['type'] !== IssueType::Task->value;
    }

    /**
     * Finds issue rows for the kanban board of a single project.
     *
     * @param \Cake\ORM\Query\SelectQuery $query The query to decorate.
     * @param array<string, mixed> $options Finder options.
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findForKanban(SelectQuery $query, array $options): SelectQuery
    {
        return $query
            ->where([
                'Issues.project_id' => $options['project_id'],
                'Issues.type' => 'issue',
            ])
            ->contain(['Statuses', 'Assignees', 'Departments'])
            ->orderBy([
                'Issues.status_id' => 'ASC',
                'Issues.position' => 'ASC',
            ]);
    }
}
