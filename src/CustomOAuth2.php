<?php

namespace Celebron\social;

use Celebron\social\attrs\WidgetSupport;
use Celebron\social\interfaces\CustomInterface;
use Celebron\social\interfaces\GetUrlsInterface;
use Celebron\social\interfaces\ToWidgetInterface;
use Celebron\social\interfaces\ToWidgetTrait;
use yii\base\InvalidConfigException;

#[WidgetSupport(true, true)]
class CustomOAuth2 extends OAuth2 implements CustomInterface, ToWidgetInterface
{
    use ToWidgetTrait;

    private string $_icon = '';
    private ?string $_name;
    private bool $_visible = true;


    /** @var ?\Closure - method(RequestCode $request, CustomOAuth2 $object) */
    public ?\Closure $closureCode = null;
    /** @var ?\Closure - method(RequestToken $request, CustomOAth2 $object) */
    public ?\Closure $closureToken = null;
    /** @var ?\Closure - method(RequestId $request, CustomOAth2 $object) */
    public ?\Closure $closureId = null;

    /**
     * @inheritDoc
     */
    public function requestCode (RequestCode $request): void
    {
        if(isset($this->closureCode)) {
            call_user_func($this->closureCode, $request, $this);
        }
        throw new InvalidConfigException('Property $closureCode is null');
    }

    /**
     * @inheritDoc
     */
    public function requestToken (RequestToken $request): void
    {
        if(isset($this->closureToken)) {
            call_user_func($this->closureToken, $request, $this);
        }
        throw new InvalidConfigException('Property $closureToken is null');
    }

    public function requestId (RequestId $request): \Celebron\social\Response
    {
        if(isset($this->closureId)) {
            return call_user_func($this->closureId, $request, $this);
        }
        throw new InvalidConfigException('Property $closureId is null');
    }
}