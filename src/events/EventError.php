<?php

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