<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\socialSource;

use Celebron\socialSource\interfaces\SocialUserInterface;
use yii\web\IdentityInterface;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Secure
{
    public function __construct (protected \Closure|string $secureMethod)
    {

    }

    public function secure(Social $social, IdentityInterface&SocialUserInterface $user):bool
    {
        if(!is_callable($this->secureMethod)) {
            $closure = \Closure::fromCallable([$user, $this->secureMethod]);
        } else {
            $closure = $this->secureMethod;
        }

        return $closure->call($user, $social);
    }
}