<?php
declare(strict_types=1);

namespace App\Action;

use Cake\Datasource\EntityInterface;
use Cake\Datasource\QueryInterface;
use IdentityBridge\ValueObject\AuthenticatedRequestIdentity;

/**
 * Shared authorization helpers for custom Crud actions.
 */
trait AuthorizedCrudActionTrait
{
    /**
     * Adds the authenticated user id to request data when the action is configured
     * with a `currentUserField` and the field is currently empty.
     *
     * @param array<string, mixed> $data The request data.
     * @return array<string, mixed>
     */
    protected function requestDataWithCurrentUser(array $data): array
    {
        $field = $this->currentUserField();
        if ($field === null) {
            return $data;
        }

        $userId = $this->authenticatedUserId();
        if ($userId === null || !$this->shouldPopulateCurrentUserField($data, $field)) {
            return $data;
        }

        $data[$field] = $userId;

        return $data;
    }

    /**
     * @return string|null
     */
    private function currentUserField(): ?string
    {
        $field = $this->getConfig('currentUserField');

        return is_string($field) && $field !== '' ? $field : null;
    }

    /**
     * @return int|null
     */
    private function authenticatedUserId(): ?int
    {
        $identity = $this->_controller()->getRequest()->getAttribute('identityBridge.identity');
        if (!$identity instanceof AuthenticatedRequestIdentity) {
            return null;
        }

        $userId = $identity->user['id'] ?? null;
        if (is_int($userId)) {
            return $userId;
        }
        if (is_string($userId) && ctype_digit($userId)) {
            return (int)$userId;
        }

        return null;
    }

    /**
     * @param array<string, mixed> $data
     * @param string $field
     * @return bool
     */
    private function shouldPopulateCurrentUserField(array $data, string $field): bool
    {
        if (!array_key_exists($field, $data)) {
            return true;
        }

        return $data[$field] === null || $data[$field] === '';
    }

    /**
     * Authorizes the current table resource for the resolved action.
     *
     * @param string $defaultAction The default policy action name.
     * @return void
     */
    protected function authorizeTable(string $defaultAction): void
    {
        $this->_controller()->Authorization->authorize(
            $this->_controller()->fetchTable(),
            $this->authorizationAction($defaultAction),
        );
    }

    /**
     * Authorizes the loaded entity for the resolved action.
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity to authorize.
     * @param string $defaultAction The default policy action name.
     * @return void
     */
    protected function authorizeEntity(EntityInterface $entity, string $defaultAction): void
    {
        $this->_controller()->Authorization->authorize(
            $entity,
            $this->authorizationAction($defaultAction),
        );
    }

    /**
     * Applies an authorization scope to the query for the resolved action.
     *
     * @param \Cake\Datasource\QueryInterface $query The query to scope.
     * @param string $defaultAction The default policy action name.
     * @return \Cake\Datasource\QueryInterface
     */
    protected function applyAuthorizationScope(QueryInterface $query, string $defaultAction): QueryInterface
    {
        /** @var \Cake\Datasource\QueryInterface */
        return $this->_controller()->Authorization->applyScope(
            $query,
            $this->authorizationAction($defaultAction),
        );
    }

    /**
     * Returns the configured policy action or the default one for the Crud action.
     *
     * @param string $defaultAction The fallback policy action name.
     * @return string
     */
    protected function authorizationAction(string $defaultAction): string
    {
        $configuredAction = $this->getConfig('authorizationAction');

        return is_string($configuredAction) && $configuredAction !== ''
            ? $configuredAction
            : $defaultAction;
    }
}
