<?php
declare(strict_types=1);

namespace App\Controller\Api;

/**
 * Read-only Crud-backed activity log controller.
 *
 * @property \App\Model\Table\ActivityLogTable $ActivityLog
 */
class ActivityLogController extends AppController
{
    /**
     * @return void
     * @throws \Exception
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->useAuthorizedCrudActions();
        $this->Crud->disable(['view', 'add', 'edit', 'delete']);
    }
}
