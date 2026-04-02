<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Enum\ProjectMemberRole;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
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
        $this->addEnumValidation($validator, 'role', ProjectMemberRole::class);

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

    /**
     * Prevents demoting the final admin of a project.
     *
     * @param \Cake\Event\EventInterface $event The beforeSave event.
     * @param \Cake\Datasource\EntityInterface $entity The member being saved.
     * @param \ArrayObject<string, mixed> $options Save options.
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        if (
            !$entity->isNew() &&
            $entity->isDirty('role') &&
            $entity->getOriginal('role') === ProjectMemberRole::Admin->value &&
            $entity->get('role') !== ProjectMemberRole::Admin->value &&
            $this->isLastAdmin((int)$entity->get('project_id'), (int)$entity->get('id'))
        ) {
            $entity->setError('role', __('A project must have at least one admin.'));
            $event->stopPropagation();
            $event->setResult(false);
        }
    }

    /**
     * Prevents deleting the final admin of a project.
     *
     * @param \Cake\Event\EventInterface $event The beforeDelete event.
     * @param \Cake\Datasource\EntityInterface $entity The member being deleted.
     * @param \ArrayObject<string, mixed> $options Delete options.
     * @return void
     */
    public function beforeDelete(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        if (
            $entity->get('role') === ProjectMemberRole::Admin->value &&
            $this->isLastAdmin((int)$entity->get('project_id'), (int)$entity->get('id'))
        ) {
            $entity->setError('role', __('A project must have at least one admin.'));
            $event->stopPropagation();
            $event->setResult(false);
        }
    }

    /**
     * Checks whether the given member is the final admin for a project.
     *
     * @param int $projectId The project identifier.
     * @param int $memberId The member identifier to exclude from the count.
     * @return bool
     */
    public function isLastAdmin(int $projectId, int $memberId): bool
    {
        $adminCount = $this->find()
            ->where([
                'project_id' => $projectId,
                'role' => ProjectMemberRole::Admin->value,
                'id !=' => $memberId,
            ])
            ->count();

        return $adminCount === 0;
    }
}
