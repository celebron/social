<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social\interfaces;

interface ViewerInterface
{
    public const VIEW_LOGIN = 'login';
    public const VIEW_MANAGEMENT = 'management';

    public function getSupportManagement (): bool;

    public function getSupportLogin (): bool;
}