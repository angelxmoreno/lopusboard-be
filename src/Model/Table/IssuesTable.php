<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Issues Model
 *
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\BelongsTo $Projects
 * @property \App\Model\Table\IssuesTable&\Cake\ORM\Association\BelongsTo $ParentIssues
 * @property \App\Model\Table\StatusesTable&\Cake\ORM\Association\BelongsTo $Statuses
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Assignees
 * @property \App\Model\Table\DepartmentsTable&\Cake\ORM\Association\BelongsTo $Departments
 * @property \App\Model\Table\CommentsTable&\Cake\ORM\Association\HasMany $Comments
 * @property \App\Model\Table\IssueRelationsTable&\Cake\ORM\Association\HasMany $IssueRelations
 * @property \App\Model\Table\IssuesTable&\Cake\ORM\Association\HasMany $ChildIssues
 *
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
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class IssuesTable extends Table
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

        $this->belongsTo('Projects', [
            'foreignKey' => 'project_id',
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
        $this->hasMany('Comments', [
            'foreignKey' => 'issue_id',
        ]);
        $this->hasMany('IssueRelations', [
            'foreignKey' => 'issue_id',
        ]);
        $this->hasMany('ChildIssues', [
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
        $rules->add($rules->existsIn(['parent_id'], 'ParentIssues'), ['errorField' => 'parent_id']);
        $rules->add($rules->existsIn(['status_id'], 'Statuses'), ['errorField' => 'status_id']);
        $rules->add($rules->existsIn(['assignee_id'], 'Assignees'), ['errorField' => 'assignee_id']);
        $rules->add($rules->existsIn(['department_id'], 'Departments'), ['errorField' => 'department_id']);

        return $rules;
    }
}
