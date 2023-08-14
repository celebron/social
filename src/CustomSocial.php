<?php

namespace Celebron\socialSource;
use Celebron\socialSource\interfaces\CustomRequestInterface;
use Celebron\socialSource\interfaces\ViewerInterface;
use Celebron\socialSource\responses\Id;
use yii\base\UnknownClassException;
use yii\base\UnknownPropertyException;

/**
 *
 * @property-read bool $supportLogin
 * @property-read bool $supportRegister
 */
class CustomSocial extends Social implements CustomRequestInterface, ViewerInterface
{

    public ?\Closure $handler = null;

    /**
     * @throws UnknownPropertyException
     * @throws UnknownClassException
     */
    public function request (?string $code, State $state): ?Id
    {
        if($this->handler !== null) {
            $result = $this->handler->call($this, $code, $state);
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