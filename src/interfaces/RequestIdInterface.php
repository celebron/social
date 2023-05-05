<?php

namespace Celebron\social\interfaces;

use Celebron\social\RequestId;

/**
 * @property mixed $id
 */
interface RequestIdInterface
{

    public function getUriInfo(): string;

    public function requestId(RequestId $request): mixed;

    public function getId(): mixed;
    public function setId(mixed $id);
}