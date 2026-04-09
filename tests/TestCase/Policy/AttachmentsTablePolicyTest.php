<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Table\AttachmentsTable;
use App\Policy\AttachmentsTablePolicy;
use App\Test\Support\Auth\TestAuthorizationIdentity;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;

class AttachmentsTablePolicyTest extends TestCase
{
    use LocatorAwareTrait;

    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Projects',
        'app.ProjectMembers',
        'app.Attachments',
    ];

    public function testScopeIndexRestrictsResultsToAccessibleProjects(): void
    {
        $policy = new AttachmentsTablePolicy();

        $results = $policy
            ->scopeIndex(new TestAuthorizationIdentity(['id' => 2]), $this->attachments()->find())
            ->all()
            ->extract('id')
            ->toList();

        $this->assertSame([1], $results);
    }

    private function attachments(): AttachmentsTable
    {
        /** @var \App\Model\Table\AttachmentsTable */
        return $this->getTableLocator()->get(AttachmentsTable::class);
    }
}
