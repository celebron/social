<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social\interfaces;

use Celebron\source\social\responses\Id;
use Celebron\source\social\State;

interface RequestInterface extends SocialInterface
{
    public function request(?string $code, State $state, ...$args):?Id;
}