<?php

namespace Celebron\social\eventArgs;

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
        public SocialController $action
        , public string $method
        , array $config = [])
    {
        parent::__construct($config);

    }

}