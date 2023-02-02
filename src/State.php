<?php

namespace Celebron\social;

use yii\base\Exception;
use yii\helpers\Json;

class State implements \Stringable
{

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

    public function __toString ()
    {
        return $this->encode();
    }

    public static function create($method, string $state = null):self
    {
        $obj = new self();
        $obj->method = $method;
        $obj->state = $state;
        return $obj;
    }

    public static function open(string|\Stringable $stateBase64):self
    {
        return (new self())->decode($stateBase64);
    }
}