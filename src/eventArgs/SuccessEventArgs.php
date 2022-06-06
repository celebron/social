<?php

namespace Celebron\social\eventArgs;

use Celebron\social\SocialAction;
use yii\base\Event;

class SuccessEventArgs extends Event
{
    public mixed $result = null;

    public function __construct (public SocialAction $action, $config = [])
    {
        parent::__construct($config);
    }

}