<?php

namespace Celebron\social\args;

use Celebron\social\SocialConfiguration;
use Celebron\social\State;

class RequestArgs
{

    /**
     * @param SocialConfiguration $config
     * @param string|null $code
     * @param State $state
     */
    public function __construct (
        public SocialConfiguration $config,
        public ?string             $code,
        public State               $state)
    {
    }
}