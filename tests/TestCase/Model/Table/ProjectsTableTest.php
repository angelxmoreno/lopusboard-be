<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ProjectsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ProjectsTable Test Case
 */
class ProjectsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ProjectsTable
     */
    protected $Projects;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Projects',
        'app.Users',
        'app.Statuses',
        'app.Departments',
        'app.ProjectMembers',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Projects') ? [] : ['className' => ProjectsTable::class];
        $this->Projects = $this->getTableLocator()->get('Projects', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Projects);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ProjectsTable::validationDefault()
     */
    public function testInitializeAssociations(): void
    {
        $this->assertSame('Users', $this->Projects->getAssociation('Creators')->getClassName());
        $this->assertSame('created_by', $this->Projects->getAssociation('Creators')->getForeignKey());
    }

    /**
     * @return void
     */
    public function testBuildRulesRejectsUnknownCreator(): void
    {
        $project = $this->Projects->newEntity(
            [
                'name' => 'Validation Project',
                'slug' => 'validation-project',
                'created_by' => 999,
            ],
            ['accessibleFields' => ['*' => true]],
        );

        $this->assertFalse($this->Projects->save($project));
        $this->assertArrayHasKey('created_by', $project->getErrors());
    }

    /**
     * @return void
     */
    public function testSaveSeedsDefaultStatusesDepartmentsAndAdminMember(): void
    {
        $project = $this->Projects->newEntity([
            'name' => 'Behavior Project',
            'slug' => 'behavior-project',
            'created_by' => 1,
        ], ['accessibleFields' => ['created_by' => true]]);

        $saved = $this->Projects->save($project);

        $this->assertNotFalse($saved);
        $this->assertSame(5, $this->Projects->Statuses->find()->where(['project_id' => $project->id])->count());
        $this->assertSame(4, $this->Projects->Departments->find()->where(['project_id' => $project->id])->count());
        $this->assertSame(1, $this->Projects->ProjectMembers->find()->where([
            'project_id' => $project->id,
            'user_id' => 1,
            'role' => 'admin',
        ])->count());
    }
}
