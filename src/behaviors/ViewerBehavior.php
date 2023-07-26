<?php

namespace Celebron\socialSource\behaviors;

/**
 *
 * @property null|string $icon
 * @property string $name
 * @property bool $visible
 */
class ViewerBehavior extends Behavior
{
    private ?string $_name;
    public function getName():string
    {
        return $this->_name ?? $this->socialName;
    }
    public function setName(string $value):void
    {
        $this->_name = $value;
    }

    private bool $_visible = true;
    public function getVisible():bool
    {
        return $this->_visible;
    }
    public function setVisible(bool $value):void
    {
        $this->_visible = $value;
    }

    private ?string $_icon = null;
    public function getIcon():?string
    {
        return $this->_icon ?? '';
    }
    public function setIcon(string $value):void
    {
        $this->_icon = $value;
    }
}