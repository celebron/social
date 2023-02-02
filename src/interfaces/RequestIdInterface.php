<?php

namespace Celebron\social\interfaces;

use Celebron\social\RequestId;

interface RequestIdInterface
{

    public function getUriInfo(): string;

    public function requestId(RequestId $request): mixed;
}