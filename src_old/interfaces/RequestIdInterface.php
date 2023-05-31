<?php

namespace Celebron\social\old\interfaces;

use Celebron\social\old\RequestId;

/**
 * @property mixed $id
 */
interface RequestIdInterface
{

    public function getUriInfo(): string;

    public function requestId(RequestId $request): mixed;
}