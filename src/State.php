<?php

namespace Celebron\socialSource;

use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\base\UnknownMethodException;
use yii\helpers\Json;
use yii\helpers\StringHelper;

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

    public ?string $action = null;
    public string $random;
    public ?string $state = null;

    /**
     * @throws Exception
     */
    protected function __construct ()
    {
        $this->random = self::generateRandomString(10);
    }

    protected function decode($stateBase64): self
    {
        $data = Json::decode(StringHelper::base64UrlDecode($stateBase64));
        $this->action = $data['a'];
        $this->state = $data['s'];
        $this->random = $data['r'];
        return $this;
    }

    protected function encode(): string
    {
        $data = [
            'a' => $this->action,
            's' => $this->state,
            'r' => $this->random,
        ];
        return StringHelper::base64UrlEncode(Json::encode($data));
    }

    public function equalRandom(null|string|self $random): bool
    {
        if($random instanceof self) {
            $random = $random->random;
        }
        return $this->random === $random;
    }

    public function normalizeMethod():string
    {
        if(str_contains($this->action, '-')) {
            $split = [];
            foreach (explode('-', $this->action) as $exp) {
                $split[] = ucfirst($exp);
            }
            $method = implode($split);
        } else {
            $method = ucfirst($this->action);
        }
        return  $method;
    }

    public function equalAction(string $action):bool
    {
        return $this->action === $action;
    }

    public function __toString ()
    {
        return $this->encode();
    }

    public static function create(string $action, ?string $state = null):self
    {
        $obj = new self();
        $obj->action = strip_tags($action);
        $obj->state = strip_tags($state);
        return $obj;
    }

    public static function open(string|\Stringable $stateBase64):self
    {
        return (new self())->decode($stateBase64);
    }

    public static function generateRandomString(int $length = 32):string
    {
        if ($length < 1) {
            throw new InvalidArgumentException('First parameter ($length) must be greater than 0');
        }
        $bytes = random_bytes($length);
        return substr(StringHelper::base64UrlEncode($bytes), 0, $length);
    }

    public static function __callStatic ($methodName, $arguments)
    {
        $prefix = 'create';
        $prefixLen = strlen($prefix);

        if(StringHelper::startsWith($methodName, $prefix)) {
            $name = strtolower(substr($methodName, $prefixLen));
            return static::create($name, $arguments[0] ?? null);
        }
        throw new UnknownMethodException('Calling unknown method: ' . static::class . "::$methodName()");
    }

    public function __call ($methodName, $arguments)
    {
        $isPrefix = 'is';
        $isPrefixLen = strlen($isPrefix);
        if(StringHelper::startsWith($methodName, $isPrefix)) {
            $name = strtolower(substr($methodName, $isPrefixLen));
            return $this->equalAction($name);
        }
    }

}