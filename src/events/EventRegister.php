<?php

namespace Celebron\socialSource\events;

use Celebron\socialSource\interfaces\SocialInterface;
use yii\base\Component;
use yii\base\Event;

class EventRegister extends Event
{
    public bool $support = true;
    public function __construct (
        public Component&SocialInterface $object,
                                         $config = [])
    {
        parent::__construct($config);
    }
}