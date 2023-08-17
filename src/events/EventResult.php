<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social\events;

use Celebron\source\social\HandlerController;
use Celebron\source\social\Response;
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