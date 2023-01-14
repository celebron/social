<?php

namespace Celebron\social\interfaces;

interface ToWidgetInterface
{
    public function getName():string;
    public function getIcon():string;

    public function getVisible():bool;
}