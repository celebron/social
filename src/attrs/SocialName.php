<?php

namespace Celebron\social\attrs;

#[\Attribute(\Attribute::TARGET_CLASS)]
class SocialName
{
    public function __construct (public string $name)
    {
    }
}