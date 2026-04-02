<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * ProjectMembers Model
 *
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\BelongsTo $Projects
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @method \App\Model\Entity\ProjectMember newEmptyEntity()
 * @method \App\Model\Entity\ProjectMember newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ProjectMember> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ProjectMember get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ProjectMember findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ProjectMember patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ProjectMember> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ProjectMember|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ProjectMember saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\ProjectMember>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ProjectMember>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ProjectMember>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ProjectMember> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ProjectMember>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ProjectMember>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ProjectMember>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ProjectMember> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ProjectMembersTable extends AppTable
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

        $this->setTable('project_members');
        $this->setDisplayField('role');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Projects', [
            'foreignKey' => 'project_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
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
            ->nonNegativeInteger('user_id')
            ->notEmptyString('user_id');

        $validator
            ->scalar('role')
            ->notEmptyString('role');

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
        $rules->add(
            $rules->isUnique(['project_id', 'user_id']),
            [
                'errorField' => 'project_id',
                'message' => __('This combination of project_id and user_id already exists'),
            ],
        );
        $rules->add($rules->existsIn(['project_id'], 'Projects'), ['errorField' => 'project_id']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }
}
