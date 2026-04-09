<?php
declare(strict_types=1);

namespace App\Controller\Api;

/**
 * Thin Crud-backed wiki page revisions controller.
 *
 * @property \App\Model\Table\WikiPageRevisionsTable $WikiPageRevisions
 */
class WikiPageRevisionsController extends AppController
{
    /**
     * @return void
     * @throws \Exception
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->useAuthorizedCrudActions();
        $this->Crud->disable(['add', 'edit', 'delete']);
    }
}
