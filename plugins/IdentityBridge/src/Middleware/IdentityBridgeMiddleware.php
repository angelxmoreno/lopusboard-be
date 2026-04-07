<?php
declare(strict_types=1);

namespace IdentityBridge\Middleware;

use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use IdentityBridge\Enum\AuthenticationMode;
use IdentityBridge\Exception\AuthenticationException;
use IdentityBridge\Exception\ConfigurationException;
use IdentityBridge\Service\IdentityAuthenticator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Route-aware middleware for applying IdentityBridge authentication.
 */
final class IdentityBridgeMiddleware implements MiddlewareInterface
{
    private readonly AuthenticationMode $mode;

    /**
     * @var array<string, bool>
     */
    private array $overrides;

    public function __construct(
        private readonly IdentityAuthenticator $authenticator,
        array $config = [],
    ) {
        $config = array_replace((array)Configure::read('IdentityBridge', []), $config);

        $mode = AuthenticationMode::tryFrom(
            (string)($config['mode'] ?? AuthenticationMode::ProtectedByDefault->value)
        );
        if ($mode === null) {
            throw new ConfigurationException('Invalid IdentityBridge authentication mode.');
        }

        $overrides = $config['overrides'] ?? [];
        if (!is_array($overrides)) {
            throw new ConfigurationException('IdentityBridge overrides must be an array.');
        }

        foreach ($overrides as $pattern => $isProtected) {
            if (!is_string($pattern) || $pattern === '') {
                throw new ConfigurationException('IdentityBridge override keys must be non-empty strings.');
            }
            if (!is_bool($isProtected)) {
                throw new ConfigurationException(
                    'IdentityBridge override values must be booleans.'
                );
            }
        }

        $this->mode = $mode;
        $this->overrides = $overrides;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $routeTarget = $this->getRouteTarget($request);
        if ($routeTarget === null || !$this->isProtectedRoute($routeTarget)) {
            return $handler->handle($request);
        }

        try {
            $authenticatedIdentity = $this->authenticator->authenticate($this->extractBearerToken($request));
        } catch (AuthenticationException $exception) {
            return $this->buildUnauthorizedResponse($exception);
        }

        $request = $request
            ->withAttribute('identityBridge.remoteIdentity', $authenticatedIdentity->remoteIdentity)
            ->withAttribute('identityBridge.user', $authenticatedIdentity->user);

        return $handler->handle($request);
    }

    private function getRouteTarget(ServerRequestInterface $request): ?string
    {
        $params = [];
        if ($request instanceof ServerRequest) {
            $params = [
                'prefix' => $request->getParam('prefix'),
                'controller' => $request->getParam('controller'),
                'action' => $request->getParam('action'),
            ];
        } else {
            $params = [
                'prefix' => $request->getAttribute('prefix'),
                'controller' => $request->getAttribute('controller'),
                'action' => $request->getAttribute('action'),
            ];
        }

        $controller = $params['controller'];
        $action = $params['action'];
        if (!is_string($controller) || $controller === '' || !is_string($action) || $action === '') {
            return null;
        }

        $segments = [];
        $prefix = $params['prefix'];
        if (is_string($prefix) && $prefix !== '') {
            $segments[] = $prefix;
        }

        $segments[] = $controller;
        $segments[] = $action;

        return implode('/', $segments);
    }

    private function isProtectedRoute(string $routeTarget): bool
    {
        foreach ($this->overrides as $pattern => $isProtected) {
            if (fnmatch($pattern, $routeTarget)) {
                return $isProtected;
            }
        }

        return $this->mode === AuthenticationMode::ProtectedByDefault;
    }

    private function extractBearerToken(ServerRequestInterface $request): string
    {
        $header = $request->getHeaderLine('Authorization');
        if (preg_match('/^\s*Bearer\s+(.+?)\s*$/i', $header, $matches) === 1) {
            return $matches[1];
        }

        return '';
    }

    private function buildUnauthorizedResponse(AuthenticationException $exception): ResponseInterface
    {
        $body = json_encode(['message' => $exception->getMessage()], JSON_THROW_ON_ERROR);

        return (new Response())
            ->withStatus(401)
            ->withType('application/json')
            ->withStringBody($body);
    }
}
