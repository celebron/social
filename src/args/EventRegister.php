<?php

namespace Celebron\social\args;

use Celebron\social\AuthBase;
use yii\base\Event;

class EventRegister extends Event
{
    public bool $support = false;
    public ?AuthBase $social = null;
}