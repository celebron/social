<?php

namespace Celebron\social\interfaces;

use Celebron\social\SocialResponse;
use Celebron\social\State;

interface SocialAuthInterface
{
    public function request(?string $code, State $state):SocialResponse;
}