<?php
declare(strict_types=1);

namespace App\Action;

use Cake\Http\Response;
use Crud\Action\EditAction;

/**
 * Crud edit action with entity authorization.
 */
class AuthorizedEditAction extends EditAction
{
    use AuthorizedCrudActionTrait;

    /**
     * @param string|int|null $id Record id
     * @return void
     */
    protected function _get(string|int|null $id = null): void
    {
        $subject = $this->_subject();
        $subject->set(['id' => $id]);
        $entity = $this->_findRecord($id, $subject);
        $this->authorizeEntity($entity, 'edit');
        $subject->set(['entity' => $entity]);

        $this->_trigger('beforeRender', $subject);
    }

    /**
     * @param string|int|null $id Record id
     * @return \Cake\Http\Response|null
     */
    protected function _put(string|int|null $id = null): ?Response
    {
        $subject = $this->_subject();
        $subject->set(['id' => $id]);

        $entity = $this->_findRecord($id, $subject);
        $this->authorizeEntity($entity, 'edit');

        $entity = $this->_model()->patchEntity(
            $entity,
            $this->_request()->getData(),
            $this->saveOptions(),
        );

        $subject->set(['entity' => $entity]);
        $this->_trigger('beforeSave', $subject);
        /** @phpstan-ignore argument.type */
        if (call_user_func([$this->_model(), $this->saveMethod()], $entity, $this->saveOptions())) {
            return $this->_success($subject);
        }

        $this->_error($subject);

        return null;
    }
}
