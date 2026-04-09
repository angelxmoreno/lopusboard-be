<?php
declare(strict_types=1);

namespace App\Controller\Api;

/**
 * Thin Crud-backed attachments controller.
 *
 * @property \App\Model\Table\AttachmentsTable $Attachments
 */
class AttachmentsController extends AppController
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
            'accessibleFields' => ['project_id' => true, 'uploaded_by' => true],
        ]);
        $this->Crud->action('add')->setConfig('currentUserField', 'uploaded_by');
    }
}
