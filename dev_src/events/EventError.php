<?php

namespace Celebron\social\dev\events;


use Celebron\social\dev\SocialController;

/**
 * Параметры для события error
 * @property \Celebron\social\dev\SocialController $action - Контролер SocialController
 * @property  \Exception|null $exception - Исключение или null - (ошибка не связана с исключением)
 */
class EventError extends EventResult
{
    /**
     * Конструктор
     * @param \Celebron\social\dev\SocialController $acton - объект контролера SocialController
     * @param \Exception|null $exception - объект исключения или null - (ошибка не связана с исключением)
     * @param array $config - Стандартный конфиг Yii2
     */
    public function __construct (
        SocialController $acton,
        public ?\Exception $exception,
        array $config = []
    ){
        parent::__construct($acton, null, $config);
    }

    /**
     * Вернуться назад
     * @param string|array|null $defaultUrl
     * @return void
     */
    public function goBack(string|array $defaultUrl = null): void
    {
        $this->result = true;
        $this->action->goBack($defaultUrl);
    }

    /**
     * Вернуться домой
     * @return void
     */
    public function goHome(): void
    {
        $this->result = true;
        $this->goHome();
    }

    /**
     * Редирект
     * @param string|array $url - куда
     * @return void
     */
    public function redirect(string|array $url): void
    {
        $this->result = true;
        $this->action->redirect($url);
    }
}