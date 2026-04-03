<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Enum\ActivityAction;
use App\Model\Enum\ActivitySubjectType;
use App\Model\Enum\AttachmentLinkableType;
use Cake\Datasource\EntityInterface;
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
        $this->addBehavior('ActivityLog', [
            'subjectType' => function (EntityInterface $entity): ?string {
                return match ($entity->get('linkable_type')) {
                    AttachmentLinkableType::Issue->value => ActivitySubjectType::Issue->value,
                    AttachmentLinkableType::WikiPage->value => ActivitySubjectType::WikiPage->value,
                    default => null,
                };
            },
            'subjectIdField' => 'linkable_id',
            'actorResolver' => function (EntityInterface $entity): ?int {
                $attachment = $this->Attachments->find()
                    ->select(['uploaded_by'])
                    ->where(['Attachments.id' => $entity->get('attachment_id')])
                    ->disableHydration()
                    ->first();

                return $attachment === null ? null : (int)$attachment['uploaded_by'];
            },
            'projectResolver' => function (EntityInterface $entity): ?int {
                $attachment = $this->Attachments->find()
                    ->select(['project_id'])
                    ->where(['Attachments.id' => $entity->get('attachment_id')])
                    ->disableHydration()
                    ->first();

                return $attachment === null ? null : (int)$attachment['project_id'];
            },
            'createAction' => ActivityAction::FileAttached->value,
            'updateAction' => ActivityAction::Updated->value,
            'deleteAction' => ActivityAction::Deleted->value,
        ]);

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
        $this->addEnumValidation($validator, 'linkable_type', AttachmentLinkableType::class);

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
        $rules->add([$this, 'linkableTargetExists'], 'linkableTargetExists', [
            'errorField' => 'linkable_id',
            'message' => __('Linkable target does not exist for the selected type.'),
        ]);

        return $rules;
    }

    /**
     * Checks that the polymorphic target exists.
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity being validated.
     * @param array<string, mixed> $options Rule options.
     * @return bool
     */
    public function linkableTargetExists(EntityInterface $entity, array $options): bool
    {
        $linkableType = $entity->get('linkable_type');
        $linkableId = $entity->get('linkable_id');
        if (!is_string($linkableType) || $linkableId === null) {
            return true;
        }

        $table = match ($linkableType) {
            AttachmentLinkableType::Issue->value => 'Issues',
            AttachmentLinkableType::WikiPage->value => 'WikiPages',
            default => null,
        };
        if ($table === null) {
            return true;
        }

        return $this->fetchTable($table)->exists(['id' => $linkableId]);
    }
}
