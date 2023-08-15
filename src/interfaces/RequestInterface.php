<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\socialSource\interfaces;

use Celebron\socialSource\responses\Id;
use Celebron\socialSource\State;

interface RequestInterface extends SocialInterface
{
    public function request(?string $code, State $state, ...$args):?Id;
}