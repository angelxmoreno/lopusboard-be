<?php
declare(strict_types=1);

namespace App\Controller\Api;

/**
 * Thin Crud-backed comments controller.
 *
 * @property \App\Model\Table\CommentsTable $Comments
 */
class CommentsController extends AppController
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
            'accessibleFields' => ['issue_id' => true, 'user_id' => true],
        ]);
        $this->Crud->action('add')->setConfig('currentUserField', 'user_id');
    }
}
