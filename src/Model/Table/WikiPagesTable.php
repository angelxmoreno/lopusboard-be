<?php
declare(strict_types=1);

namespace App\Model\Table;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\Utility\Text;
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
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class WikiPagesTable extends AppTable
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
     * Normalizes wiki page data before validation runs.
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @param \ArrayObject<string, mixed> $data The marshalled data.
     * @param \ArrayObject<string, mixed> $options Marshal options.
     * @return void
     */
    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options): void
    {
        $title = $data['title'] ?? null;
        $slug = $data['slug'] ?? null;

        if (is_string($title) && $title !== '' && (!is_string($slug) || trim($slug) === '')) {
            $data['slug'] = strtolower(Text::slug($title, ['replacement' => '-']));
        }
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
            $rules->isUnique(['project_id', 'slug']),
            [
                'errorField' => 'project_id',
                'message' => __('This combination of project_id and slug already exists'),
            ],
        );
        $rules->add($rules->existsIn(['project_id'], 'Projects'), ['errorField' => 'project_id']);
        $rules->add($rules->existsIn(['created_by'], 'Creators'), ['errorField' => 'created_by']);
        $rules->add($rules->existsIn(['last_edited_by'], 'LastEditors'), ['errorField' => 'last_edited_by']);
        $rules->add($rules->existsIn(['parent_id'], 'ParentPages'), ['errorField' => 'parent_id']);
        $rules->add([$this, 'parentBelongsToProject'], 'parentBelongsToProject', [
            'errorField' => 'parent_id',
            'message' => __('Parent page must belong to the same project as the wiki page.'),
        ]);

        return $rules;
    }

    /**
     * Checks that the selected parent page belongs to the same project.
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity being validated.
     * @param array<string, mixed> $options Rule options.
     * @return bool
     */
    public function parentBelongsToProject(EntityInterface $entity, array $options): bool
    {
        $parentId = $entity->get('parent_id');
        $projectId = $entity->get('project_id');
        if ($parentId === null || $projectId === null) {
            return true;
        }

        $parentPage = $this->ParentPages
            ->find()
            ->select(['project_id'])
            ->where(['ParentPages.id' => $parentId])
            ->disableHydration()
            ->first();

        return $parentPage !== null && (int)$parentPage['project_id'] === (int)$projectId;
    }

    /**
     * Finds wiki pages for a project in tree order.
     *
     * @param \Cake\ORM\Query\SelectQuery $query The query to decorate.
     * @param array<string, mixed> $options Finder options.
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findTree(SelectQuery $query, array $options): SelectQuery
    {
        return $query
            ->where(['WikiPages.project_id' => $options['project_id']])
            ->orderBy(['WikiPages.position' => 'ASC']);
    }
}
