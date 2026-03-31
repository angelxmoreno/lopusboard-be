<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Attachments Model
 *
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\BelongsTo $Projects
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Uploaders
 * @property \App\Model\Table\AttachmentLinksTable&\Cake\ORM\Association\HasMany $AttachmentLinks
 *
 * @method \App\Model\Entity\Attachment newEmptyEntity()
 * @method \App\Model\Entity\Attachment newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Attachment> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Attachment get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Attachment findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Attachment patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Attachment> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Attachment|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Attachment saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Attachment>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Attachment>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Attachment>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Attachment> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Attachment>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Attachment>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Attachment>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Attachment> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AttachmentsTable extends Table
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

        $this->setTable('attachments');
        $this->setDisplayField('storage_provider');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Projects', [
            'foreignKey' => 'project_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Uploaders', [
            'className' => 'Users',
            'foreignKey' => 'uploaded_by',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('AttachmentLinks', [
            'foreignKey' => 'attachment_id',
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
            ->scalar('storage_provider')
            ->notEmptyString('storage_provider');

        $validator
            ->scalar('filename')
            ->maxLength('filename', 500)
            ->requirePresence('filename', 'create')
            ->notEmptyString('filename');

        $validator
            ->scalar('mime_type')
            ->maxLength('mime_type', 100)
            ->allowEmptyString('mime_type');

        $validator
            ->allowEmptyString('file_size');

        $validator
            ->scalar('storage_path')
            ->maxLength('storage_path', 1000)
            ->allowEmptyString('storage_path');

        $validator
            ->scalar('external_url')
            ->maxLength('external_url', 1000)
            ->allowEmptyString('external_url');

        $validator
            ->scalar('external_id')
            ->maxLength('external_id', 255)
            ->allowEmptyString('external_id');

        $validator
            ->nonNegativeInteger('uploaded_by')
            ->requirePresence('uploaded_by', 'create')
            ->notEmptyString('uploaded_by');

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
        $rules->add($rules->existsIn(['uploaded_by'], 'Uploaders'), ['errorField' => 'uploaded_by']);

        return $rules;
    }
}
