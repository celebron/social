<?php

namespace Celebron\social\eventArgs;

use Celebron\social\Social;
use yii\base\Event;

class RegisterEventArgs extends Event
{
    public function __construct (public Social $social, array $config = [])
    {
        parent::__construct($config);
    }
}