<?php

namespace Celebron\socialSource\interfaces;

interface ViewerInterface
{
    public function getSupportRegister():bool;
    public function getSupportLogin():bool;
}