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
    /**
     * @param string|array|null $defaultUrl
     * @return void
     */
    public function goBack(string|array $defaultUrl = null): void
    {
        $this->result = true;
        $this->controller->goBack($defaultUrl);
    }

    /**
     * @return void
     */
    public function goHome(): void
    {
        $this->result = true;
        $this->controller->goHome();
    }

    /**
     * @param string|array $url - РєСѓРґР°
     * @return void
     */
    public function redirect(string|array $url): void
    {
        $this->result = true;
        $this->controller->redirect($url);
    }

}