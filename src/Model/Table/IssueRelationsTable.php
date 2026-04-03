<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Enum\ActivityAction;
use App\Model\Enum\ActivitySubjectType;
use Cake\Datasource\EntityInterface;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * IssueRelations Model
 *
 * @property \App\Model\Table\IssuesTable&\Cake\ORM\Association\BelongsTo $Issues
 * @property \App\Model\Table\IssuesTable&\Cake\ORM\Association\BelongsTo $RelatedIssues
 * @method \App\Model\Entity\IssueRelation newEmptyEntity()
 * @method \App\Model\Entity\IssueRelation newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\IssueRelation> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\IssueRelation get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\IssueRelation findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\IssueRelation patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\IssueRelation> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\IssueRelation|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\IssueRelation saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\IssueRelation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\IssueRelation>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\IssueRelation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\IssueRelation> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\IssueRelation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\IssueRelation>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\IssueRelation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\IssueRelation> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class IssueRelationsTable extends AppTable
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

        $this->setTable('issue_relations');
        $this->setDisplayField('type');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('ActivityLog', [
            'subjectType' => function (EntityInterface $entity): ?string {
                $issue = $this->Issues->find()
                    ->select(['type'])
                    ->where(['Issues.id' => $entity->get('issue_id')])
                    ->disableHydration()
                    ->first();

                if ($issue === null) {
                    return null;
                }

                return $issue['type'] === 'task'
                    ? ActivitySubjectType::Task->value
                    : ActivitySubjectType::Issue->value;
            },
            'subjectIdField' => 'issue_id',
            'projectResolver' => function (EntityInterface $entity): ?int {
                $issue = $this->Issues->find()
                    ->select(['project_id'])
                    ->where(['Issues.id' => $entity->get('issue_id')])
                    ->disableHydration()
                    ->first();

                return $issue === null ? null : (int)$issue['project_id'];
            },
            'createAction' => ActivityAction::RelationAdded->value,
            'updateAction' => ActivityAction::Updated->value,
            'deleteAction' => ActivityAction::RelationRemoved->value,
        ]);

        $this->belongsTo('Issues', [
            'foreignKey' => 'issue_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('RelatedIssues', [
            'foreignKey' => 'related_issue_id',
            'className' => 'Issues',
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
            ->nonNegativeInteger('issue_id')
            ->notEmptyString('issue_id');

        $validator
            ->nonNegativeInteger('related_issue_id')
            ->notEmptyString('related_issue_id');

        $validator
            ->scalar('type')
            ->requirePresence('type', 'create')
            ->notEmptyString('type');

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
            $rules->isUnique(['issue_id', 'related_issue_id', 'type']),
            [
                'errorField' => 'issue_id',
                'message' => __('This combination of issue_id, related_issue_id and type already exists'),
            ],
        );
        $rules->add($rules->existsIn(['issue_id'], 'Issues'), ['errorField' => 'issue_id']);
        $rules->add($rules->existsIn(['related_issue_id'], 'RelatedIssues'), ['errorField' => 'related_issue_id']);

        return $rules;
    }
}
