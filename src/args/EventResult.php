<?php

namespace Celebron\social\args;


use Celebron\social\Response;
use Celebron\social\SocialResponse;
use Celebron\social\SocialController;
use yii\base\Event;
use yii\helpers\ArrayHelper;

/**
 * Параметры для события registerSuccess и loginSuccess
 */
class EventResult extends Event
{
    /** @var mixed|null - вывод */
    public mixed $result = null;

    public function __construct (
        public SocialController $action,
        public ?Response $response,
        array $config = []
    )
    {
        parent::__construct($config);
    }

    public function render(string $view, array $params=[]): void
    {
        $params = ArrayHelper::merge([
            'response' => $this->response,
        ], $params);
        $this->result = $this->action->render($view, $params);
    }

    public function renderAjax(string $view, array $params=[]): void
    {
        $params = ArrayHelper::merge([
            'response' => $this->response,
        ], $params);
        $this->result = $this->action->renderAjax($view, $params);
    }

    public function content(string $content):void
    {
        $this->result = $this->action->renderContent($content);
    }

}