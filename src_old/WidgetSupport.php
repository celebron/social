<?php

namespace Celebron\src_old;
use yii\base\NotSupportedException;

#[\Attribute(\Attribute::TARGET_CLASS)]
class WidgetSupport
{

    public function __construct (public bool $register = true, public bool $login = true)
    {
        if(!$this->register && !$this->login) {
            throw new NotSupportedException('Both values must not be disabled.');
        }
    }
}