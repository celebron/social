<?php

namespace Celebron\social\interfaces;

trait ToWidgetTrait
{
    private ?string $_name;
    private string $_icon = '';
    private bool $_visible = true;
    public function getName (): string
    {
        return $this->_name  ?? static::socialName(); ;
    }

    public function setName(?string $name):void
    {
        $this->_name = $name;
    }

    public function getIcon (): string
    {
        return $this->_icon;
    }

    public function setIcon(string $icon):void
    {
        $this->_icon = $icon;
    }

    public function getVisible (): bool
    {
        return $this->_visible;
    }

    public function setVisible(bool $visible):void
    {
        $this->_visible = $visible;
    }
}