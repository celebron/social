<?php

namespace Celebron\social\interfaces;

trait ToWidgetTrait
{
    public ?string $name;
    public string $icon = '';
    public bool $visible = true;

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