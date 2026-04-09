<?php
declare(strict_types=1);

namespace App\Action;

use Cake\Http\Response;
use Crud\Action\AddAction;

/**
 * Crud add action with table authorization.
 */
class AuthorizedAddAction extends AddAction
{
    use AuthorizedCrudActionTrait;

    /**
     * @return void
     */
    protected function _get(): void
    {
        $this->authorizeTable('add');

        parent::_get();
    }

    /**
     * @return \Cake\Http\Response|null
     */
    protected function _post(): ?Response
    {
        $this->authorizeTable('add');

        $entity = $this->_entity($this->_request()->getData(), $this->saveOptions());
        $this->authorizeEntity($entity, 'add');

        return parent::_post();
    }

    /**
     * @return \Cake\Http\Response|null
     */
    protected function _put(): ?Response
    {
        $this->authorizeTable('add');

        $entity = $this->_entity($this->_request()->getData(), $this->saveOptions());
        $this->authorizeEntity($entity, 'add');

        return parent::_put();
    }
}
