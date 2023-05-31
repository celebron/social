<?php

namespace Celebron\social\old\eventArgs;

use Celebron\social\old\SocialConfiguration;
use Celebron\social\old\State;

class RequestArgs
{
    public bool $requested = false;
    public readonly string $actionMethod;

    public function __construct (
        public readonly SocialConfiguration $config,
        public readonly ?string $code,
        public readonly State $state,
    )
    {
        $this->actionMethod = 'action' . $this->state->normalizeMethod();
    }


}