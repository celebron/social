<?php

namespace Celebron\socialSource\interfaces;

use Celebron\socialSource\responses\IdResponse;
use Celebron\socialSource\State;

interface RequestInterface extends SocialInterface
{
    public function request(?string $code, State $state):?IdResponse;
}