<?php

namespace Celebron\src_old;

#[\Attribute(\Attribute::TARGET_CLASS)]
class SocialName
{
    public function __construct (public string $name)
    {
    }
}