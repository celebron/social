<?php

namespace Celebron\socialSource\events;

use Celebron\socialSource\HandlerController;
use Celebron\socialSource\Response;
use yii\base\Event;

class EventResult extends Event
{
    public mixed $result = null;

    public function __construct (
        public readonly HandlerController $controller,
        public readonly ?Response $response ,
        $config = []
    )
    {
        parent::__construct($config);
    }
}