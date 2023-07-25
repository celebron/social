<?php

namespace Celebron\social\interfaces;

interface SocialViewInterface
{
    public function getSupportRegister():bool;
    public function getSupportLogin():bool;
}