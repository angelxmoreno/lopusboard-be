<?php
declare(strict_types=1);

namespace App\Controller\Api;

/**
 * Thin Crud-backed projects controller.
 *
 * Crud maps the standard REST actions for now.
 *
 * @property \App\Model\Table\ProjectsTable $Projects
 */
class ProjectsController extends AppController
{
    /**
     * @return void
     * @throws \Exception
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->useAuthorizedCrudActions();
    }
}
