<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * WikiPageLinks Model
 *
 * @property \App\Model\Table\WikiPagesTable&\Cake\ORM\Association\BelongsTo $SourcePages
 * @property \App\Model\Table\WikiPagesTable&\Cake\ORM\Association\BelongsTo $TargetPages
 *
 * @method \App\Model\Entity\WikiPageLink newEmptyEntity()
 * @method \App\Model\Entity\WikiPageLink newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\WikiPageLink> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\WikiPageLink get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\WikiPageLink findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\WikiPageLink patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\WikiPageLink> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\WikiPageLink|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\WikiPageLink saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\WikiPageLink>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\WikiPageLink>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\WikiPageLink>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\WikiPageLink> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\WikiPageLink>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\WikiPageLink>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\WikiPageLink>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\WikiPageLink> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class WikiPageLinksTable extends Table
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

        $this->setTable('wiki_page_links');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('SourcePages', [
            'foreignKey' => 'source_page_id',
            'className' => 'WikiPages',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('TargetPages', [
            'foreignKey' => 'target_page_id',
            'className' => 'WikiPages',
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
            ->nonNegativeInteger('source_page_id')
            ->notEmptyString('source_page_id');

        $validator
            ->nonNegativeInteger('target_page_id')
            ->notEmptyString('target_page_id');

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
        $rules->add($rules->isUnique(['source_page_id', 'target_page_id']), ['errorField' => 'source_page_id', 'message' => __('This combination of source_page_id and target_page_id already exists')]);
        $rules->add($rules->existsIn(['source_page_id'], 'SourcePages'), ['errorField' => 'source_page_id']);
        $rules->add($rules->existsIn(['target_page_id'], 'TargetPages'), ['errorField' => 'target_page_id']);

        return $rules;
    }
}
