<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social\traits;

use Celebron\source\social\interfaces\ViewerInterface;
use Celebron\source\social\Social;
use yii\base\InvalidConfigException;

trait ViewerTrait
{
    private ?string $_name;

    public function getName (): string
    {
        return $this->_name ?? $this->socialName;
    }

    public function setName (string $value): void
    {
        $this->_name = $value;
    }


    private bool $_visible = true;

    public function getVisible (): bool
    {
        return $this->_visible;
    }

    public function setVisible (bool $value): void
    {
        $this->_visible = $value;
    }

    protected ?string $_icon;

    public function getIcon (): string
    {
        /** @var Social&ViewerInterface $this */
        return $this->_icon ??
            (method_exists($this, 'defaultIcon')
                ? $this->defaultIcon()
                : $this->configure->defaultIcon
            );
    }

    public function setIcon (string $value): void
    {
        throw new InvalidConfigException('Write configuration only');
    }
}