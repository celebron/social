<?php

namespace Celebron\social\interfaces;

use Celebron\social\RequestId;

interface RequestIdInterface
{
    public function requestId(RequestId $request): mixed;
    public function getUriInfo(): string;
}