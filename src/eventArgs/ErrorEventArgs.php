<?php

namespace Celebron\social\eventArgs;


use Celebron\social\SocialController;

/**
 * Параметры для события error
 * @property SocialController $action - Контролер SocialController
 * @property  \Exception|null $exception - Исключение или null - (ошибка не связана с исключением)
 */
class ErrorEventArgs extends SuccessEventArgs
{
    /**
     * Конструктор
     * @param SocialController $acton - объект контролера SocialController
     * @param \Exception|null $exception - объект исключения или null - (ошибка не связана с исключением)
     * @param array $config - Стандартный конфиг Yii2
     */
    public function __construct (public SocialController $acton, public ?\Exception $exception, array $config = [])
    {
        parent::__construct($this->acton, $config);
    }

    /**
     * Вернуться назад
     * @param string|array|null $defaultUrl
     * @return void
     */
    public function goBack(string|array $defaultUrl = null): void
    {
        $this->result = null;
        $this->action->goBack($defaultUrl);
    }

    /**
     * Вернуться домой
     * @return void
     */
    public function goHome(): void
    {
        $this->result = null;
        $this->goHome();
    }

    /**
     * Редирект
     * @param string|array $url - куда
     * @return void
     */
    public function redirect(string|array $url): void
    {
        $this->result = null;
        $this->action->redirect($url);
    }
}