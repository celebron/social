<?php

namespace Celebron\socialSource;
use Celebron\socialSource\interfaces\CustomRequestInterface;
use Celebron\socialSource\interfaces\ViewerInterface;
use yii\base\UnknownClassException;
use yii\base\UnknownPropertyException;

/**
 *
 * @property-read bool $supportLogin
 * @property-read bool $supportRegister
 */
class CustomRequest extends Request implements CustomRequestInterface, ViewerInterface
{

    public ?\Closure $handler = null;

    /**
     * @throws UnknownPropertyException
     * @throws UnknownClassException
     */
    public function request (?string $code, State $state): ?ResponseSocial
    {
        if($this->handler !== null) {
            $result = $this->handler->call($this, $code, $state);
            if($result instanceof ResponseSocial) {
                return $result;
            }
            throw new UnknownClassException('Class not extend ' . ResponseSocial::class);
        }
        throw new UnknownPropertyException('Property $handler is null');
    }

    public function getSupportLogin (): bool
    {
       return true;
    }

    public function getSupportRegister (): bool
    {
        return true;
    }
}