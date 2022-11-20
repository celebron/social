<?php

namespace Celebron\social;

#[\Attribute(\Attribute::TARGET_CLASS)]
class SocialName
{
    public function __construct (public string $name)
    {
    }
}