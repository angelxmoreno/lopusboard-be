<?php
declare(strict_types=1);

namespace App\Action;

use Cake\Http\Exception\UnauthorizedException;
use Crud\Action\BaseAction;

/**
 * Crud action that exposes the current authenticated API identity.
 */
class IdentityMeAction extends BaseAction
{
    /**
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'enabled' => true,
        'api' => [
            'methods' => ['get'],
        ],
    ];

    /**
     * Returns the current authenticated identity and local user summary.
     *
     * @return void
     */
    protected function _handle(): void
    {
        /** @var \IdentityBridge\ValueObject\AuthenticatedRequestIdentity|null $identity */
        $identity = $this->_controller()->IdentityBridge->getAuthenticatedRequestIdentity();
        if ($identity === null) {
            throw new UnauthorizedException('Authenticated identity is required.');
        }

        $this->_controller()->set('data', [
            'remoteIdentity' => $identity->remoteIdentity,
            'user' => $this->serializeUser($identity->user),
        ]);
        $this->_controller()->set('success', true);
        $this->_controller()->viewBuilder()->setOption('serialize', ['success', 'data']);
    }

    /**
     * @param object $user The resolved local user object.
     * @return array<string, mixed>
     */
    protected function serializeUser(object $user): array
    {
        return [
            'id' => $user->id ?? null,
            'name' => $user->name ?? null,
            'email' => $user->email ?? null,
            'avatarUrl' => $user->avatar_url ?? null,
        ];
    }
}
