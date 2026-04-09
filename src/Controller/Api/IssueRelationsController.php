<?php
declare(strict_types=1);

namespace App\Controller\Api;

/**
 * Thin Crud-backed issue relations controller.
 *
 * @property \App\Model\Table\IssueRelationsTable $IssueRelations
 */
class IssueRelationsController extends AppController
{
    /**
     * @return void
     * @throws \Exception
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->useAuthorizedCrudActions();
        $this->Crud->action('add')->setConfig('saveOptions', [
            'accessibleFields' => ['issue_id' => true],
        ]);
    }
}
