<?php

namespace Celebron\social\interfaces;


use Celebron\social\State;

trait ToWidgetTrait
{
    public function getName (): string
    {
        return $this->_name  ?? static::socialName();
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

    public function urlLogin(?string $state = null): string
    {
        return $this->url(State::ACTION_LOGIN, $state);
    }

    public function urlRegister(?string $state= null): string
    {
        return $this->url(State::ACTION_REGISTER, $state);
    }

    public function urlDelete(?string $state= null): string
    {
        return $this->url(State::ACTION_DELETE, $state);
    }

}