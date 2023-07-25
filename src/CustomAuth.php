<?php

namespace Celebron\social;
use Celebron\social\interfaces\AbstractSocialAuth;
use Celebron\social\interfaces\CustomInterface;
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