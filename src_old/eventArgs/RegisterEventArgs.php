<?php

namespace Celebron\social\old\eventArgs;

use Celebron\social\old\AuthBase;
use yii\base\Event;

class RegisterEventArgs extends Event
{
    public bool $support = false;
    public function __construct (public AuthBase $social, array $config = [])
    {
        parent::__construct($config);
    }
}