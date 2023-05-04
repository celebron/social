<?php

namespace Celebron\social\socials;

use Celebron\social\AuthBase;
use Celebron\social\SocialConfiguration;

class Telegram extends AuthBase
{
    final public function actionLogin(SocialConfiguration $config) : bool
    {
        var_dump($_POST);
        return true;
    }

//    final public function actionRegister() : bool
//    {
//
//    }
}