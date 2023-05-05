<?php

namespace Celebron\social\interfaces;

use Celebron\social\eventArgs\RequestArgs;

interface AuthActionInterface
{
    public function actionLogin(RequestArgs $args) : bool;
    public function actionRegister(RequestArgs $args) : bool;
    public function actionDelete(RequestArgs $args) : bool;
}