<?php

namespace Celebron\social\eventArgs;


use Celebron\social\SocialController;


class ErrorEventArgs extends SuccessEventArgs
{

    public function __construct (public SocialController $acton, public ?\Exception $exception, $config = [])
    {
        parent::__construct($this->acton, $config);
    }

    public function goBack(string|array $defaultUrl = null)
    {
        $this->result = null;
        $this->action->goBack($defaultUrl);
    }

    public function goHome()
    {
        $this->result = null;
        $this->goHome();
    }

    public function redirect(string|array $url)
    {
        $this->result = null;
        $this->action->redirect($url);
    }
}