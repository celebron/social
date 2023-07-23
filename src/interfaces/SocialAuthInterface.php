<?php

namespace Celebron\social\interfaces;

use Celebron\social\Response;
use Celebron\social\SocialController;
use Celebron\social\SocialResponse;
use Celebron\social\State;

interface SocialAuthInterface
{
    public function getActive():bool;
    public function setActive(bool $value):void;
    public function request(?string $code, State $state):SocialResponse;
    public function success(SocialController $action, Response $response): mixed;
    public function failed(SocialController $action, Response $response): mixed;
}