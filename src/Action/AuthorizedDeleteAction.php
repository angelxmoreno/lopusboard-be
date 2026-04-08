<?php
declare(strict_types=1);

namespace App\Action;

use Cake\Http\Response;
use Crud\Action\DeleteAction;

/**
 * Crud delete action with entity authorization.
 */
class AuthorizedDeleteAction extends DeleteAction
{
    use AuthorizedCrudActionTrait;

    /**
     * @param string|int|null $id Record id
     * @return \Cake\Http\Response|null
     */
    protected function _post(string|int|null $id = null): ?Response
    {
        $subject = $this->_subject();
        $subject->set(['id' => $id]);

        $entity = $this->_findRecord($id, $subject);
        $this->authorizeEntity($entity, 'delete');

        $event = $this->_trigger('beforeDelete', $subject);
        if ($event->isStopped()) {
            return $this->_stopped($subject);
        }

        $method = $this->getConfig('deleteMethod');
        if ($this->_model()->$method($entity)) {
            $this->_success($subject);
        } else {
            $this->_error($subject);
        }

        return $this->_redirect($subject, ['action' => 'index']);
    }
}
