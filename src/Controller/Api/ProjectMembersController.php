<?php
declare(strict_types=1);

namespace App\Controller\Api;

/**
 * Thin Crud-backed project members controller.
 *
 * @property \App\Model\Table\ProjectMembersTable $ProjectMembers
 */
class ProjectMembersController extends AppController
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
            'accessibleFields' => ['project_id' => true],
        ]);
    }
}
