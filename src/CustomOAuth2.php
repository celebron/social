<?php

namespace Celebron\social;

use Celebron\social\attrs\WidgetSupport;
use Celebron\social\interfaces\AbstractOAuth2;
use Celebron\social\interfaces\CustomInterface;
use yii\base\InvalidConfigException;

#[WidgetSupport(true, true)]
class CustomOAuth2 extends AbstractOAuth2 implements CustomInterface
{
    private string $_icon = '';
    private ?string $_name;
    private bool $_visible = true;


    /** @var ?\Closure - method(RequestCode $request, CustomOAuth2 $object) */
    public ?\Closure $handleCode = null;
    /** @var ?\Closure - method(RequestToken $request, CustomOAth2 $object) */
    public ?\Closure $handleToken = null;
    /** @var ?\Closure - method(RequestId $request, CustomOAth2 $object) */
    public ?\Closure $handleId = null;

    /**
     * @inheritDoc
     */
    public function requestCode (RequestCode $request): void
    {
        if($this->handleCode !== null) {
            call_user_func($this->handleCode, $request, $this);
        } else {
            throw new InvalidConfigException('Property $handleCode is null');
        }
    }

    /**
     * @inheritDoc
     */
    public function requestToken (RequestToken $request): void
    {
        if($this->handleToken !== null) {
            call_user_func($this->handleToken, $request, $this);
        } else {
            throw new InvalidConfigException('Property $handleToken is null');
        }
    }

    public function requestId (RequestId $request): \Celebron\social\SocialResponse
    {
        if($this->handleId !== null) {
            return call_user_func($this->handleId, $request, $this);
        }
        throw new InvalidConfigException('Property $handleId is null');
    }
}