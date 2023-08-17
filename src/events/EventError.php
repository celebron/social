<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social\events;

use Celebron\source\social\HandlerController;
use Celebron\source\social\Response;

class EventError extends EventResult
{
    public function __construct (
        HandlerController $controller,
        public \Exception $exception,
        $config = []
    )
    {
        parent::__construct($controller, null, $config);
    }
}