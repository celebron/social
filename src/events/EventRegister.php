<?php

namespace Celebron\social\events;

use Celebron\social\AuthBase;
use Celebron\social\interfaces\SocialAuthInterface;
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