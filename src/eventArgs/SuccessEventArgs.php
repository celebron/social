<?php

namespace Celebron\social\eventArgs;

use yii\base\Event;
use yii\web\Controller;

class SuccessEventArgs extends Event
{
    public mixed $result;

    public function __construct (public Controller $controller, $config = [])
    {
        parent::__construct($config);
    }

}