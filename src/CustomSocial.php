<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social;
use Celebron\source\social\interfaces\CustomRequestInterface;
use Celebron\source\social\interfaces\ViewerInterface;
use Celebron\source\social\responses\Id;
use Celebron\source\social\traits\ViewerTrait;
use yii\base\UnknownClassException;
use yii\base\UnknownPropertyException;

/**
 *
 * @property-read bool $supportLogin
 * @property-read bool $supportManagement
 * @property-read bool $supportRegister
 */
class CustomSocial extends Social implements CustomRequestInterface, ViewerInterface
{
    use ViewerTrait;
    public ?\Closure $handler = null;

    /**
     * @throws UnknownPropertyException
     * @throws UnknownClassException
     */
    public function request (?string $code, State $state, ...$args): ?Id
    {
        if($this->handler !== null) {
            $result = $this->handler->call($this, $code, $state, $args);
            if($result instanceof Id) {
                return $result;
            }
            throw new UnknownClassException('Class not extend ' . Id::class);
        }
        throw new UnknownPropertyException('Property $handler is null');
    }

    public function getSupportLogin (): bool
    {
       return true;
    }

    public function getSupportManagement (): bool
    {
        return true;
    }
}