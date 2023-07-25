<?php

namespace Celebron\social\dev\interfaces;

use Celebron\social\dev\SocialResponse;
use Celebron\social\dev\State;

interface AuthInterface extends SocialAuthInterface
{
    public function request(?string $code, State $state):SocialResponse;
}