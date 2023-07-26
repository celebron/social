<?php

namespace Celebron\socialSource\behaviors;

/**
 *
 * @property bool $active
 */
class ActiveBehavior extends Behavior
{
    private bool $_active = false;

    /**
     * @return bool
     */
    public function getActive (): bool
    {
        return $this->params['active'] ?? $this->_active;
    }
    public function setActive (bool $value): void
    {
        $this->_active = $value;
    }
}