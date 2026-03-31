<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * WikiPages Model
 *
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\BelongsTo $Projects
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Creators
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $LastEditors
 * @property \App\Model\Table\WikiPagesTable&\Cake\ORM\Association\BelongsTo $ParentPages
 * @property \App\Model\Table\WikiPagesTable&\Cake\ORM\Association\HasMany $ChildPages
 * @property \App\Model\Table\WikiPageLinksTable&\Cake\ORM\Association\HasMany $WikiPageLinks
 * @property \App\Model\Table\WikiPageRevisionsTable&\Cake\ORM\Association\HasMany $WikiPageRevisions
 *
 * @method \App\Model\Entity\WikiPage newEmptyEntity()
 * @method \App\Model\Entity\WikiPage newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\WikiPage> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\WikiPage get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\WikiPage findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\WikiPage patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\WikiPage> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\WikiPage|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\WikiPage saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\WikiPage>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\WikiPage>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\WikiPage>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\WikiPage> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\WikiPage>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\WikiPage>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\WikiPage>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\WikiPage> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class WikiPagesTable extends Table
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

        $this->setTable('wiki_pages');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Projects', [
            'foreignKey' => 'project_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Creators', [
            'className' => 'Users',
            'foreignKey' => 'created_by',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('LastEditors', [
            'className' => 'Users',
            'foreignKey' => 'last_edited_by',
        ]);
        $this->belongsTo('ParentPages', [
            'className' => 'WikiPages',
            'foreignKey' => 'parent_id',
        ]);
        $this->hasMany('ChildPages', [
            'className' => 'WikiPages',
            'foreignKey' => 'parent_id',
        ]);
        $this->hasMany('WikiPageLinks', [
            'foreignKey' => 'source_page_id',
        ]);
        $this->hasMany('WikiPageRevisions', [
            'foreignKey' => 'wiki_page_id',
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
            ->nonNegativeInteger('parent_id')
            ->allowEmptyString('parent_id');

        $validator
            ->scalar('title')
            ->maxLength('title', 500)
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        $validator
            ->scalar('slug')
            ->maxLength('slug', 500)
            ->requirePresence('slug', 'create')
            ->notEmptyString('slug');

        $validator
            ->scalar('body')
            ->maxLength('body', 4294967295)
            ->allowEmptyString('body');

        $validator
            ->decimal('position')
            ->notEmptyString('position');

        $validator
            ->nonNegativeInteger('created_by')
            ->requirePresence('created_by', 'create')
            ->notEmptyString('created_by');

        $validator
            ->nonNegativeInteger('last_edited_by')
            ->allowEmptyString('last_edited_by');

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
        $rules->add($rules->isUnique(['project_id', 'slug']), ['errorField' => 'project_id', 'message' => __('This combination of project_id and slug already exists')]);
        $rules->add($rules->existsIn(['project_id'], 'Projects'), ['errorField' => 'project_id']);
        $rules->add($rules->existsIn(['created_by'], 'Creators'), ['errorField' => 'created_by']);
        $rules->add($rules->existsIn(['last_edited_by'], 'LastEditors'), ['errorField' => 'last_edited_by']);
        $rules->add($rules->existsIn(['parent_id'], 'ParentPages'), ['errorField' => 'parent_id']);

        return $rules;
    }

    public function findTree(SelectQuery $query, array $options): SelectQuery
    {
        return $query
            ->where(['WikiPages.project_id' => $options['project_id']])
            ->orderBy(['WikiPages.position' => 'ASC']);
    }
}
