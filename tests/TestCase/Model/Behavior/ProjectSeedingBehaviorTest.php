<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Behavior;

use App\Model\Table\ProjectsTable;
use Cake\TestSuite\TestCase;

/**
 * ProjectSeedingBehavior test case.
 */
class ProjectSeedingBehaviorTest extends TestCase
{
    /**
     * @var \App\Model\Table\ProjectsTable
     */
    protected ProjectsTable $Projects;

    /**
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
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->Projects = $this->getTableLocator()->get('Projects');
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Projects);

        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testBehaviorAttachedAndSeedsDefaultProjectData(): void
    {
        $this->assertTrue($this->Projects->behaviors()->has('ProjectSeeding'));

        $project = $this->Projects->newEntity([
            'name' => 'Seeded Project',
            'slug' => 'seeded-project',
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
        $this->assertSame(1, $this->Projects->Statuses->find()->where([
            'project_id' => $project->id,
            'name' => 'Todo',
            'is_default' => true,
        ])->count());
        $this->assertSame(1, $this->Projects->Statuses->find()->where([
            'project_id' => $project->id,
            'name' => 'Done',
            'is_done' => true,
        ])->count());
    }
}
