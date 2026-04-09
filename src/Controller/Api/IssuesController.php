<?php
declare(strict_types=1);

namespace App\Controller\Api;

/**
 * Thin Crud-backed issues controller.
 *
 * Crud maps the standard REST actions for now.
 *
 * @property \App\Model\Table\IssuesTable $Issues
 */
class IssuesController extends AppController
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
            'accessibleFields' => ['project_id' => true, 'created_by' => true],
        ]);
        $this->Crud->action('add')->setConfig('currentUserField', 'created_by');
    }
}
