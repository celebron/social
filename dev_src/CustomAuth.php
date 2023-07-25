<?php

namespace Celebron\social\dev;
use Celebron\social\dev\interfaces\AbstractSocialAuth;
use Celebron\social\dev\interfaces\CustomInterface;
use yii\base\InvalidConfigException;

class CustomAuth extends AbstractSocialAuth implements CustomInterface
{
    public ?\Closure $handler = null;

    /**
     * @throws InvalidConfigException
     */
    public function request (?string $code, State $state): SocialResponse
    {
        if($this->handler !== null) {
           return call_user_func($this->handler, $code, $state, $this);
        }
        throw new InvalidConfigException('Property $handler is null');
    }
}