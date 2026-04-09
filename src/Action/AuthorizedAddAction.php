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
        $saveOptions = $this->saveOptions();
        $data = $this->_request()->getData();
        if (!is_array($data)) {
            $data = [];
        }

        $entity = $this->_entity($this->requestDataWithCurrentUser($data), $saveOptions);
        $this->authorizeEntity($entity, 'add');
        $saveMethod = $this->saveMethod();
        $subject = $this->_subject([
            'entity' => $entity,
            'saveMethod' => $saveMethod,
            'saveOptions' => $saveOptions,
        ]);

        $event = $this->_trigger('beforeSave', $subject);
        if ($event->isStopped()) {
            return $this->_stopped($subject);
        }

        $saveCallback = [$this->_model(), $saveMethod];
        /** @phpstan-ignore argument.type */
        if (call_user_func($saveCallback, $entity, $saveOptions)) {
            return $this->_success($subject);
        }

        $this->_error($subject);

        return null;
    }

    /**
     * @return \Cake\Http\Response|null
     */
    protected function _put(): ?Response
    {
        return $this->_post();
    }
}
