<?php

namespace Celebron\social;

use yii\base\Exception;
use yii\base\UnknownMethodException;
use yii\helpers\Json;

/**
 * Создания State параметра для запроса в OAuth2 server
 * @example
 *  static::create{MethodName}($state) = static::create({MethodName}, $state)
 */
class State implements \Stringable
{

    public const METHOD_LOGIN = 'login';
    public const METHOD_REGISTER = 'register';
    public const METHOD_DELETE = 'delete';

    public ?string $method = null;
    public string $random;
    public ?string $state = null;

    /**
     * @throws Exception
     */
    protected function __construct ()
    {
        $this->random = \Yii::$app->security->generateRandomString();
    }

    protected function decode($stateBase64): self
    {
        $data = Json::decode(base64_decode($stateBase64));
        $this->method = $data['m'];
        $this->state = $data['s'];
        $this->random = $data['r'];
        return $this;
    }

    public function normalizeMethod():string
    {
        if(str_contains($this->method, '-')) {
            $split = [];
            foreach (explode('-', $this->method) as $exp) {
                $split[] = ucfirst($exp);
            }
            $method = implode($split);
        } else {
            $method = ucfirst($this->method);
        }
        return  $method;
    }

    protected function encode(): string
    {
        $data = [
            'm' => $this->method,
            's' => $this->state,
            'r' => $this->random,
        ];
        return base64_encode(Json::encode($data));
    }

    public function equalRandom(null|string|self $random): bool
    {
        if($random instanceof self) {
            $random = $random->random;
        }
        return $this->random === $random;
    }

    public function equalAction(string $action):bool
    {
        return $this->method === $action;
    }

    public function isLogin():bool
    {
        return $this->equalAction(self::METHOD_LOGIN);
    }

    public function isRegister():bool
    {
        return $this->equalAction(self::METHOD_REGISTER);
    }

    public function isDelete():bool
    {
        return $this->equalAction(self::METHOD_DELETE);
    }

    public function __toString ()
    {
        return $this->encode();
    }

    public static function create(string $method, ?string $state = null):self
    {
        $obj = new self();
        $obj->method = strip_tags($method);
        $obj->state = strip_tags($state);
        return $obj;
    }

    public static function open(string|\Stringable $stateBase64):self
    {
        return (new self())->decode($stateBase64);
    }

    public static function __callStatic ($methodName, $arguments)
    {
        $prefix = 'start';
        $prefixLen = strlen($prefix);

        if(str_starts_with($methodName, $prefix)) {
            return static::create(substr($methodName, $prefixLen), $arguments[0] ?? null);
        }
        throw new UnknownMethodException('Calling unknown method: ' . static::class . "::$methodName()");
    }

}