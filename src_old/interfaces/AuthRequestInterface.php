<?php

namespace Celebron\social\old\interfaces;

use Celebron\social\old\eventArgs\RequestArgs;
use Celebron\social\old\SocialController;

interface AuthRequestInterface
{
    public function Request(\ReflectionMethod $method, RequestArgs $args):void;
}