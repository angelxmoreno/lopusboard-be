<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\IssueRelationsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\IssueRelationsTable Test Case
 */
class IssueRelationsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\IssueRelationsTable
     */
    protected $IssueRelations;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.ActivityLog',
        'app.IssueRelations',
        'app.Issues',
        'app.Projects',
        'app.Statuses',
        'app.Users',
        'app.Departments',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('IssueRelations') ? [] : ['className' => IssueRelationsTable::class];
        $this->IssueRelations = $this->getTableLocator()->get('IssueRelations', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->IssueRelations);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\IssueRelationsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $relation = $this->IssueRelations->newEntity(
            [
                'issue_id' => 1,
                'related_issue_id' => 2,
                'type' => '',
            ],
            ['accessibleFields' => ['*' => true]],
        );

        $this->assertArrayHasKey('type', $relation->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\IssueRelationsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $duplicate = $this->IssueRelations->newEntity(
            [
                'issue_id' => 1,
                'related_issue_id' => 1,
                'type' => 'blocks',
            ],
            ['accessibleFields' => ['*' => true]],
        );

        $this->assertFalse($this->IssueRelations->save($duplicate, ['actor_id' => 1]));
        $this->assertArrayHasKey('issue_id', $duplicate->getErrors());
    }

    /**
     * @return void
     */
    public function testSaveWritesRelationAddedActivity(): void
    {
        $relation = $this->IssueRelations->newEntity(
            [
                'issue_id' => 1,
                'related_issue_id' => 2,
                'type' => 'depends_on',
            ],
            ['accessibleFields' => ['*' => true]],
        );

        $saved = $this->IssueRelations->save($relation, ['actor_id' => 1]);

        $this->assertNotFalse($saved);
        $activityLog = $this->getTableLocator()->get('ActivityLog');
        $this->assertSame(1, $activityLog->find()->where([
            'subject_type' => 'issue',
            'subject_id' => 1,
            'action' => 'relation_added',
        ])->count());
    }
}
