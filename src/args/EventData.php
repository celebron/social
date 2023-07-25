<?php

namespace Celebron\social\args;

class EventData extends \yii\base\Event
{


    public function __construct (public array $newData, $config = [])
    {
        parent::__construct($config);
    }
}