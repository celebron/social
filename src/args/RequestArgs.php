<?php

namespace Celebron\social\args;

use Celebron\social\SocialConfig;
use Celebron\social\State;

class RequestArgs
{

    /**
     * @param SocialConfig $config
     * @param string|null $code
     * @param State $state
     */
    public function __construct (
        public SocialConfig $config,
        public ?string      $code,
        public State        $state)
    {
    }
}