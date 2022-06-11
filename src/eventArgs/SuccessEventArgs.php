<?php

namespace Celebron\social\eventArgs;

use Celebron\social\SocialController;
use yii\base\Event;

class SuccessEventArgs extends Event
{
    public mixed $result = null;

    public function __construct (public SocialController $action, $config = [])
    {
        parent::__construct($config);
    }

}