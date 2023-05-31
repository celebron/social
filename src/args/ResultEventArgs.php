<?php

namespace Celebron\social\args;


use Celebron\social\SocialController;
use yii\base\Event;

/**
 * Параметры для события registerSuccess и loginSuccess
 */
class ResultEventArgs extends Event
{
    /** @var mixed|null - вывод */
    public mixed $result = null;

    public readonly string $method;

    /**
     * Конструктор
     * @param SocialController $action - Контроллер
     * @param RequestArgs $args
     * @param array $config
     */
    public function __construct (
        public SocialController $action,
        public readonly RequestArgs $args,
        array $config = []
    )
    {
        parent::__construct($config);
        $this->method = strtolower($args->state->method);
    }

    public function render(string $view, array $params=[]): string
    {
        return $this->action->render($view, $params);
    }

}