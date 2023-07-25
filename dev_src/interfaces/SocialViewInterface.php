<?php

namespace Celebron\social\dev\interfaces;

interface SocialViewInterface
{
    public function getSupportRegister():bool;
    public function getSupportLogin():bool;
}