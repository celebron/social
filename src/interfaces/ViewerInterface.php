<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social\interfaces;

use yii\web\View;

interface ViewerInterface
{
    public const VIEW_LOGIN = 'login';
    public const VIEW_MANAGEMENT = 'management';

    public function getSupportManagement (): bool;

    public function getSupportLogin (): bool;

    public function getViewName():string;
    public function getVisible():bool;
    public function getIcon():string;
}