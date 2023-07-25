<?php

namespace Celebron\social\dev\events;

use Celebron\social\AuthBase;
use Celebron\social\dev\interfaces\SocialAuthInterface;
use yii\base\Event;

class EventRegister extends Event
{
    public bool $support = true;

    public function __construct (
        public SocialAuthInterface $object,
        $config = []
    )
    {
        parent::__construct($config);
    }
}