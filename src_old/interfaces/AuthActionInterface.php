<?php

namespace Celebron\social\old\interfaces;

use Celebron\social\old\eventArgs\RequestArgs;

interface AuthActionInterface
{
    public function actionLogin(RequestArgs $args) : bool;
    public function actionRegister(RequestArgs $args) : bool;
    public function actionDelete(RequestArgs $args) : bool;
}