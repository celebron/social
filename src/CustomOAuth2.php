<?php

namespace Celebron\social;

use Celebron\social\attrs\WidgetSupport;
use Celebron\social\interfaces\CustomInterface;
use Celebron\social\interfaces\ToWidgetInterface;
use Celebron\social\interfaces\ToWidgetTrait;
#[WidgetSupport(true, true)]
class CustomOAuth2 extends OAuth2 implements CustomInterface, ToWidgetInterface
{
    use ToWidgetTrait;
    /** @var \Closure - method(RequestCode $request, CustomOAuth2 $object) */
    public \Closure $closureCode;
    /** @var \Closure - method(RequestToken $request, CustomOAth2 $object) */
    public \Closure $closureToken;
    /** @var \Closure - method(RequestId $request, CustomOAth2 $object) */
    public \Closure $closureId;

    /**
     * @inheritDoc
     */
    public function requestCode (RequestCode $request): void
    {
        call_user_func($this->closureCode, $request, $this);
    }

    /**
     * @inheritDoc
     */
    public function requestToken (RequestToken $request): void
    {
        call_user_func($this->closureToken, $request, $this);
    }

    public function requestId (RequestId $request): \Celebron\social\Response
    {
        return call_user_func($this->closureId, $request, $this);
    }
}