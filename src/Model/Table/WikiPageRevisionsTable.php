<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * WikiPageRevisions Model
 *
 * @property \App\Model\Table\WikiPagesTable&\Cake\ORM\Association\BelongsTo $WikiPages
 *
 * @method \App\Model\Entity\WikiPageRevision newEmptyEntity()
 * @method \App\Model\Entity\WikiPageRevision newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\WikiPageRevision> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\WikiPageRevision get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\WikiPageRevision findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\WikiPageRevision patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\WikiPageRevision> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\WikiPageRevision|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\WikiPageRevision saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\WikiPageRevision>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\WikiPageRevision>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\WikiPageRevision>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\WikiPageRevision> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\WikiPageRevision>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\WikiPageRevision>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\WikiPageRevision>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\WikiPageRevision> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class WikiPageRevisionsTable extends Table
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

        $this->setTable('wiki_page_revisions');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('WikiPages', [
            'foreignKey' => 'wiki_page_id',
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
            ->nonNegativeInteger('wiki_page_id')
            ->notEmptyString('wiki_page_id');

        $validator
            ->scalar('body')
            ->maxLength('body', 4294967295)
            ->requirePresence('body', 'create')
            ->notEmptyString('body');

        $validator
            ->nonNegativeInteger('revision_number')
            ->requirePresence('revision_number', 'create')
            ->notEmptyString('revision_number');

        $validator
            ->nonNegativeInteger('edited_by')
            ->requirePresence('edited_by', 'create')
            ->notEmptyString('edited_by');

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
        $rules->add($rules->existsIn(['wiki_page_id'], 'WikiPages'), ['errorField' => 'wiki_page_id']);

        return $rules;
    }
}
