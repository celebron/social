<?php

namespace Celebron\socialSource\interfaces;

use Celebron\socialSource\ResponseSocial;
use Celebron\socialSource\State;

interface RequestInterface extends SocialInterface
{
    public function request(?string $code, State $state):?ResponseSocial;
}