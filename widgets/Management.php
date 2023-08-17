<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\widgets\social;

use Celebron\source\social\interfaces\ViewerInterface;

class Management extends Login
{
    public string $render = ViewerInterface::VIEW_MANAGEMENT;

    public bool $idView = true;
}