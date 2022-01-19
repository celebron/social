<?php

namespace Celebron\social\eventArgs;

use yii\web\Controller;

class SuccessEventArgs extends \yii\base\Event
{
    public mixed $result;

    public function __construct (public Controller $controller, $config = [])
    {
        parent::__construct($config);
    }

}