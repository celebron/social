<?php

namespace Celebron\social\interfaces;

trait ToWidgetTrait
{

    public function getName (): string
    {
        return $this->name ?? static::socialName();
    }

    public function getIcon (): string
    {
        return $this->icon;
    }

    public function getVisible (): bool
    {
        return $this->visible;
    }


}