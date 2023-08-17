<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social\traits;

use Celebron\source\social\interfaces\ViewerInterface;
use Celebron\source\social\Social;

trait ViewerTrait
{
    use SetterTrait;

    private ?string $_name;
    public function getName():string
    {
        return $this->_name ?? $this->socialName;
    }

    private bool $_visible = true;
    public function getVisible():bool
    {
        return $this->_visible;
    }

    private ?string $_icon;
    public function getIcon():string
    {
        /** @var Social&ViewerInterface $this */
        return $this->_icon ??
            (method_exists($this, 'defaultIcon')
                ?$this->defaultIcon()
                :$this->configure->defaultIcon
            );
    }




}