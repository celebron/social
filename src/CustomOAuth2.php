<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social;

use Celebron\common\Token;
use Celebron\source\social\data\CodeData;
use Celebron\source\social\data\IdData;
use Celebron\source\social\data\TokenData;
use Celebron\source\social\interfaces\CustomRequestInterface;
use Celebron\source\social\interfaces\ViewerInterface;
use Celebron\source\social\OAuth2;
use Celebron\source\social\responses\Code;
use Celebron\source\social\responses\Id;
use Celebron\source\social\traits\ViewerTrait;
use yii\base\UnknownClassException;
use yii\base\UnknownPropertyException;

/**
 *
 * @property-read bool $supportManagement
 * @property-read bool $supportLogin
 */
class CustomOAuth2 extends OAuth2 implements interfaces\CustomRequestInterface, interfaces\ViewerInterface
{
    use ViewerTrait;

    public ?\Closure $handlerCode;
    public ?\Closure $handlerToken;
    public ?\Closure $handlerId;

    public function requestCode (CodeData $request): Code
    {
        if($this->handlerCode !== null) {
            $result = $this->handlerCode->call($this, $request);
            if($result instanceof Code) {
                return $result;
            }
            throw new UnknownClassException('Class not extend ' . Code::class);
        }
        throw new UnknownPropertyException('Property $handlerCode is null');
    }

    public function requestToken (TokenData $request): Token
    {
        if($this->handlerToken !== null) {
            $result = $this->handlerToken->call($this, $request);
            if($result instanceof Token) {
                return $result;
            }
            throw new UnknownClassException('Class not extend ' . Token::class);
        }
        throw new UnknownPropertyException('Property $handlerToken is null');
    }

    public function requestId (IdData $request): Id
    {
        if($this->handlerId !== null) {
            $result = $this->handlerId->call($this, $request);
            if($result instanceof Id) {
                return $result;
            }
            throw new UnknownClassException('Class not extend ' . Id::class);
        }
        throw new UnknownPropertyException('Property $handlerId is null');
    }

    public function getSupportManagement (): bool
    {
        return true;
    }

    public function getSupportLogin (): bool
    {
        return true;
    }
}