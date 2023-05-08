<?php

namespace Celebron\social\eventArgs;

use Celebron\social\SocialConfiguration;
use Celebron\social\State;

class RequestArgs
{
    public bool $requested = false;
    public readonly string $method;

    public function __construct (
        public readonly SocialConfiguration $config,
        public readonly ?string $code,
        public readonly State $state,
    )
    {
        $this->method = 'action' . $this->state->method;
    }


}