<?php

namespace Celebron\social\attrs;
use yii\base\NotSupportedException;

#[\Attribute(\Attribute::TARGET_CLASS)]
class WidgetSupport
{

    /**
     * @throws NotSupportedException
     */
    public function __construct (public bool $register = true, public bool $login = true)
    {
        if(!$this->register && !$this->login) {
            throw new NotSupportedException('Both values must not be disabled.');
        }
    }
}