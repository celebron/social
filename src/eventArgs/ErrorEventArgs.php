<?php

namespace Celebron\social\eventArgs;

use Celebron\social\SocialAction;
use yii\web\Controller;

class ErrorEventArgs extends SuccessEventArgs
{
    public array $errors;

    public function __construct (public SocialAction $acton, $config = [])
    {
        parent::__construct($this->acton, $config);
    }

    public function goBack(string|array $defaultUrl = null)
    {
        $this->result = null;
        $this->action->controller->goBack($defaultUrl);
    }

    public function goHome()
    {
        $this->result = null;
        $this->action->controller->goHome();
    }

    public function redirect(string|array $url)
    {
        $this->result = null;
        $this->action->controller->redirect($url);
    }
}