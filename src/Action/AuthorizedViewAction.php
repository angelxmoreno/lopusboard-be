<?php
declare(strict_types=1);

namespace App\Action;

use Crud\Action\ViewAction;

/**
 * Crud view action with entity authorization.
 */
class AuthorizedViewAction extends ViewAction
{
    use AuthorizedCrudActionTrait;

    /**
     * @param string|int|null $id Record id
     * @return void
     */
    protected function _handle(string|int|null $id = null): void
    {
        $subject = $this->_subject();
        $subject->set(['id' => $id]);

        $entity = $this->_findRecord($id, $subject);
        $this->authorizeEntity($entity, 'view');

        $this->_trigger('beforeRender', $subject);
    }
}
