<?php

namespace Celebron\social\interfaces;

use Celebron\social\eventArgs\RequestArgs;
use Celebron\social\SocialController;

interface AuthRequestInterface
{
    public function Request(\ReflectionMethod $method, RequestArgs $args):void;
}