<?php

namespace Celebron\social\attrs;
#[\Attribute(\Attribute::TARGET_METHOD)]
class Request
{
    public function __construct (public bool $request)
    {
    }
}