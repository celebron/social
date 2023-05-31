<?php

namespace Celebron\src_old;

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

    public function __toString ()
    {
        return $this->encode();
    }

    public static function create(string $method, string $state = null):self
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
}