<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\socialSource\widgets;

use Celebron\socialSource\Configuration;
use Celebron\socialSource\interfaces\ViewerInterface;

class Management extends Login
{
    public string $render = ViewerInterface::VIEW_MANAGEMENT;

    public bool $idView = true;
}