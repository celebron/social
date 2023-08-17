<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social\events;

use Celebron\source\social\interfaces\SocialInterface;
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