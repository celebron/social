<?php

namespace Celebron\social\eventArgs;

use yii\web\Controller;

class ErrorEventArgs extends SuccessEventArgs
{
    public array $errors;

    public function __construct (public string $tag, $controller, $config = [])
    {
        parent::__construct($controller, $config);
    }

    public function goBack(string|array $defaultUrl = null)
    {
        $this->result = null;
        $this->controller->goBack($defaultUrl);
    }

    public function goHome()
    {
        $this->result = null;
        $this->controller->goHome();
    }

    public function redirect(string|array $url)
    {
        $this->result = null;
        $this->controller->redirect($url);
    }
}