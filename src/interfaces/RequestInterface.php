<?php

namespace Celebron\social\interfaces;

use Celebron\social\SocialController;

interface RequestInterface
{
    public function Request(\ReflectionMethod $method, SocialController $controller):void;
}