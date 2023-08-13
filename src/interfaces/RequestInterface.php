<?php

namespace Celebron\socialSource\interfaces;

use Celebron\socialSource\responses\Id;
use Celebron\socialSource\State;

interface RequestInterface extends SocialInterface
{
    public function request(?string $code, State $state, ...$args):?Id;
}