<?php

namespace Celebron\social\args;

use Celebron\social\AuthBase;
use yii\base\Event;

class RegisterEventArgs extends Event
{
    public bool $support = false;
    public function __construct (public AuthBase $social, array $config = [])
    {
        parent::__construct($config);
    }
}