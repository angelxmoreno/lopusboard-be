<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Table\ActivityLogTable;
use App\Policy\ActivityLogTablePolicy;
use App\Test\Support\Auth\TestAuthorizationIdentity;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;

class ActivityLogTablePolicyTest extends TestCase
{
    use LocatorAwareTrait;

    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Projects',
        'app.ProjectMembers',
        'app.ActivityLog',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $activityLog = $this->activityLog();
        $entries = $activityLog->newEntities([
            [
                'project_id' => 1,
                'actor_id' => 1,
                'subject_type' => 'attachment',
                'subject_id' => 1,
                'action' => 'created',
            ],
            [
                'project_id' => 2,
                'actor_id' => 2,
                'subject_type' => 'attachment',
                'subject_id' => 2,
                'action' => 'created',
            ],
        ], [
            'accessibleFields' => [
                'project_id' => true,
                'actor_id' => true,
                'subject_type' => true,
                'subject_id' => true,
                'action' => true,
            ],
        ]);

        $activityLog->saveManyOrFail($entries);
    }

    public function testScopeIndexRestrictsResultsToAccessibleProjects(): void
    {
        $policy = new ActivityLogTablePolicy();

        $results = $policy
            ->scopeIndex(new TestAuthorizationIdentity(['id' => 2]), $this->activityLog()->find())
            ->all()
            ->extract('project_id')
            ->toList();

        $this->assertSame([1], $results);
    }

    private function activityLog(): ActivityLogTable
    {
        /** @var \App\Model\Table\ActivityLogTable */
        return $this->getTableLocator()->get(ActivityLogTable::class);
    }
}
