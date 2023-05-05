<?php

namespace Celebron\social\eventArgs;

use Celebron\social\Social;
use Celebron\social\SocialController;
use yii\base\Event;

/**
 * Параметры для события registerSuccess и loginSuccess
 */
class ResultEventArgs extends Event
{
    /** @var mixed|null - вывод */
    public mixed $result = null;


    /**
     * Конструктор
     * @param SocialController $action - Контроллер
     * @param array $config
     */
    public function __construct (
        public SocialController $action,
        public readonly RequestArgs $args,
        array $config = []
    )
    {
        parent::__construct($config);
    }

    public function render(string $view, array $params=[]): string
    {
        return $this->action->render($view, $params);
    }

}