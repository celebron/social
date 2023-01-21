<?php

namespace Celebron\social;
#[\Attribute(\Attribute::TARGET_CLASS)]
class WidgetSupport
{

    public function __construct (public bool $register = true, public bool $login = true)
    {
    }
}