<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * Projects Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Creators
 * @property \App\Model\Table\ActivityLogTable&\Cake\ORM\Association\HasMany $ActivityLog
 * @property \App\Model\Table\AttachmentsTable&\Cake\ORM\Association\HasMany $Attachments
 * @property \App\Model\Table\DepartmentsTable&\Cake\ORM\Association\HasMany $Departments
 * @property \App\Model\Table\IssuesTable&\Cake\ORM\Association\HasMany $Issues
 * @property \App\Model\Table\ProjectMembersTable&\Cake\ORM\Association\HasMany $ProjectMembers
 * @property \App\Model\Table\StatusesTable&\Cake\ORM\Association\HasMany $Statuses
 * @property \App\Model\Table\WikiPagesTable&\Cake\ORM\Association\HasMany $WikiPages
 * @method \App\Model\Entity\Project newEmptyEntity()
 * @method \App\Model\Entity\Project newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Project> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Project get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Project findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Project patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Project> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Project|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Project saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Project>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Project>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Project>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Project> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Project>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Project>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Project>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Project> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ProjectsTable extends AppTable
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

        $this->setTable('projects');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('ProjectSeeding');

        $this->belongsTo('Creators', [
            'className' => 'Users',
            'foreignKey' => 'created_by',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('ActivityLog', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('Attachments', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('Departments', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('Issues', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('ProjectMembers', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('Statuses', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('WikiPages', [
            'foreignKey' => 'project_id',
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
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('slug')
            ->maxLength('slug', 100)
            ->requirePresence('slug', 'create')
            ->notEmptyString('slug')
            ->add('slug', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

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
        $rules->add($rules->isUnique(['slug']), ['errorField' => 'slug']);
        $rules->add($rules->existsIn(['created_by'], 'Creators'), ['errorField' => 'created_by']);

        return $rules;
    }
}
