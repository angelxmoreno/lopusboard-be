<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController as BaseAppController;
use Cake\View\JsonView;
use Crud\Controller\ControllerTrait;

/**
 * API application controller.
 *
 * Crud is loaded here so HTTP API controllers can use mapped Crud actions
 * without affecting CLI tooling or non-API controllers.
 */
class AppController extends BaseAppController
{
    use ControllerTrait;

    /**
     * @return void
     * @throws \Exception
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->setRequest($this->getRequest()->withParam('_ext', 'json'));
        $this->viewBuilder()->setClassName(JsonView::class);
        $this->loadComponent('IdentityBridge.IdentityBridge');

        $this->loadComponent('Crud.Crud', [
            'actions' => [
                'Crud.Index',
                'Crud.View',
                'Crud.Add',
                'Crud.Edit',
                'Crud.Delete',
            ],
            'listeners' => [
                'Crud.Api',
                'Crud.ApiPagination',
                'Crud.ApiQueryLog',
            ],
        ]);
    }

    /**
     * Maps the standard REST actions to the app's authorization-aware Crud actions.
     *
     * Controllers should opt into this only after their table and entity
     * policies are in place.
     *
     * @return void
     */
    protected function useAuthorizedCrudActions(): void
    {
        $this->Crud->mapAction('index', ['className' => 'AuthorizedIndex']);
        $this->Crud->mapAction('view', ['className' => 'AuthorizedView']);
        $this->Crud->mapAction('add', ['className' => 'AuthorizedAdd']);
        $this->Crud->mapAction('edit', ['className' => 'AuthorizedEdit']);
        $this->Crud->mapAction('delete', ['className' => 'AuthorizedDelete']);
    }
}
