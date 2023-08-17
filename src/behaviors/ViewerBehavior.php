<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social\behaviors;

use yii\base\Behavior;

/**
 *
 * @property null|string $icon
 * @property string $name
 * @property bool $visible
 */
class ViewerBehavior extends Behavior
{
    public string $icon;
    public string $name;
    public string $visible;
}