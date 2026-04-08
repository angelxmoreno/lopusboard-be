<?php
declare(strict_types=1);

namespace App\Test\Support\Auth;

use ArrayAccess;
use ArrayObject;
use Authorization\IdentityInterface;
use Authorization\Policy\Result;
use Authorization\Policy\ResultInterface;

/**
 * Small test identity for exercising policies without the full middleware stack.
 *
 * @extends \ArrayObject<string, mixed>
 */
class TestAuthorizationIdentity extends ArrayObject implements IdentityInterface
{
    /**
     * @param array<string, mixed> $data Identity data.
     */
    public function __construct(array $data)
    {
        parent::__construct($data, ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * @param string $action The action/operation being performed.
     * @param mixed $resource The resource being operated on.
     * @return bool
     */
    public function can(string $action, mixed $resource): bool
    {
        return true;
    }

    /**
     * @param string $action The action/operation being performed.
     * @param mixed $resource The resource being operated on.
     * @return \Authorization\Policy\ResultInterface
     */
    public function canResult(string $action, mixed $resource): ResultInterface
    {
        return new Result(true);
    }

    /**
     * @param string $action The action/operation being performed.
     * @param mixed $resource The resource being operated on.
     * @param mixed ...$optionalArgs Additional arguments passed to the scope.
     * @return mixed
     */
    public function applyScope(string $action, mixed $resource, mixed ...$optionalArgs): mixed
    {
        return $resource;
    }

    /**
     * @return \ArrayAccess<string, mixed>|array<string, mixed>
     */
    public function getOriginalData(): ArrayAccess|array
    {
        return $this;
    }
}
