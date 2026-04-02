<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * AttachmentLinks Model
 *
 * @property \App\Model\Table\AttachmentsTable&\Cake\ORM\Association\BelongsTo $Attachments
 * @method \App\Model\Entity\AttachmentLink newEmptyEntity()
 * @method \App\Model\Entity\AttachmentLink newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\AttachmentLink> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AttachmentLink get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\AttachmentLink findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\AttachmentLink patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\AttachmentLink> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\AttachmentLink|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\AttachmentLink saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\AttachmentLink>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\AttachmentLink>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\AttachmentLink>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\AttachmentLink> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\AttachmentLink>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\AttachmentLink>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\AttachmentLink>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\AttachmentLink> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AttachmentLinksTable extends AppTable
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

        $this->setTable('attachment_links');
        $this->setDisplayField('linkable_type');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Attachments', [
            'foreignKey' => 'attachment_id',
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
            ->nonNegativeInteger('attachment_id')
            ->notEmptyString('attachment_id');

        $validator
            ->scalar('linkable_type')
            ->requirePresence('linkable_type', 'create')
            ->notEmptyString('linkable_type');

        $validator
            ->nonNegativeInteger('linkable_id')
            ->requirePresence('linkable_id', 'create')
            ->notEmptyString('linkable_id');

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
        $rules->add($rules->existsIn(['attachment_id'], 'Attachments'), ['errorField' => 'attachment_id']);

        return $rules;
    }
}
