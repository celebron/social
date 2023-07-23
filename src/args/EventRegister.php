<?php

namespace Celebron\social\args;

use Celebron\social\SocialAuthBase;
use yii\base\Event;

class EventRegister extends Event
{
    public bool $support = false;
    public ?SocialAuthBase $social = null;
}