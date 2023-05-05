<?php

namespace Celebron\social\interfaces;

use Celebron\social\SocialConfiguration;

interface AuthActionInterface
{
    public function actionLogin(SocialConfiguration $config) : bool;
    public function actionRegister(SocialConfiguration $config) : bool;
    public function actionDelete(SocialConfiguration $config) : bool;
}