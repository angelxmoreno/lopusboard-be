<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Enum\ActivityAction;
use App\Model\Enum\ActivitySubjectType;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * ActivityLog Model
 *
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\BelongsTo $Projects
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Actors
 * @method \App\Model\Entity\ActivityLog newEmptyEntity()
 * @method \App\Model\Entity\ActivityLog newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ActivityLog> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ActivityLog get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ActivityLog findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ActivityLog patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ActivityLog> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ActivityLog|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ActivityLog saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\ActivityLog>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ActivityLog>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ActivityLog>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ActivityLog> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ActivityLog>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ActivityLog>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ActivityLog>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ActivityLog> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ActivityLogTable extends AppTable
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

        $this->setTable('activity_log');
        $this->setDisplayField('subject_type');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Projects', [
            'foreignKey' => 'project_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Actors', [
            'foreignKey' => 'actor_id',
            'className' => 'Users',
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
            ->nonNegativeInteger('actor_id')
            ->notEmptyString('actor_id');

        $validator
            ->scalar('subject_type')
            ->requirePresence('subject_type', 'create')
            ->notEmptyString('subject_type');
        $this->addEnumValidation($validator, 'subject_type', ActivitySubjectType::class);

        $validator
            ->nonNegativeInteger('subject_id')
            ->requirePresence('subject_id', 'create')
            ->notEmptyString('subject_id');

        $validator
            ->scalar('action')
            ->requirePresence('action', 'create')
            ->notEmptyString('action');
        $this->addEnumValidation($validator, 'action', ActivityAction::class);

        $validator
            ->add('old_value', 'validJsonPayload', [
                'rule' => [$this, 'validateJsonPayload'],
                'message' => __('Old value must be valid JSON or an array payload.'),
            ])
            ->allowEmptyString('old_value');

        $validator
            ->add('new_value', 'validJsonPayload', [
                'rule' => [$this, 'validateJsonPayload'],
                'message' => __('New value must be valid JSON or an array payload.'),
            ])
            ->allowEmptyString('new_value');

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
        $rules->add($rules->existsIn(['actor_id'], 'Actors'), ['errorField' => 'actor_id']);

        return $rules;
    }

    /**
     * Validates JSON payload fields used for activity snapshots.
     *
     * @param mixed $value The field value.
     * @param array<string, mixed> $context Validation context.
     * @return bool
     */
    public function validateJsonPayload(mixed $value, array $context): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        if (is_array($value)) {
            return true;
        }
        if (!is_string($value)) {
            return false;
        }

        json_decode($value);

        return json_last_error() === JSON_ERROR_NONE;
    }
}
