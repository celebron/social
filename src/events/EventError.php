<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\socialSource\events;

use Celebron\socialSource\HandlerController;
use Celebron\socialSource\Response;

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