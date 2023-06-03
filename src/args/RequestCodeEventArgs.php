<?php

namespace Celebron\social\args;

class RequestCodeEventArgs extends \yii\base\Event
{


    public function __construct (public array $newData, $config = [])
    {
        parent::__construct($config);
    }
}