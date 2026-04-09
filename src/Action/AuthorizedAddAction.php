<?php
declare(strict_types=1);

namespace App\Action;

use Cake\Http\Response;
use Crud\Action\AddAction;
use IdentityBridge\ValueObject\AuthenticatedRequestIdentity;

/**
 * Crud add action with table authorization.
 */
class AuthorizedAddAction extends AddAction
{
    use AuthorizedCrudActionTrait;

    /**
     * @return array<string, mixed>
     */
    private function requestData(): array
    {
        $data = $this->_request()->getData();
        if (!is_array($data)) {
            return [];
        }

        $field = $this->getConfig('currentUserField');
        if (!is_string($field) || $field === '') {
            return $data;
        }

        $identity = $this->_controller()->getRequest()->getAttribute('identityBridge.identity');
        if (!$identity instanceof AuthenticatedRequestIdentity) {
            return $data;
        }

        $user = $identity->user;
        $userId = $user['id'] ?? null;
        if (!is_int($userId) && !(is_string($userId) && ctype_digit($userId))) {
            return $data;
        }

        if (array_key_exists($field, $data) && $data[$field] !== null && $data[$field] !== '') {
            return $data;
        }

        $data[$field] = is_int($userId) ? $userId : (int)$userId;

        return $data;
    }

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
        $entity = $this->_entity($this->requestData(), $this->saveOptions());
        $this->authorizeEntity($entity, 'add');
        $saveMethod = $this->saveMethod();
        $saveOptions = $this->saveOptions();
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
