<?php

namespace Celebron\social\dev\attrs;

#[\Attribute(\Attribute::TARGET_CLASS)]
class SocialName
{
    public function __construct (public string $name)
    {
    }
}