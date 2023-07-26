<?php

namespace Celebron\socialSource\events;

use Celebron\socialSource\interfaces\SocialRequestInterface;
use yii\base\Component;
use yii\base\Event;

class EventRegister extends Event
{
    public bool $support = true;
    public function __construct (
        public Component&SocialRequestInterface $object,
        $config = [])
    {
        parent::__construct($config);
    }
}