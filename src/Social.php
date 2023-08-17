<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social;

use Celebron\source\social\events\EventResult;
use Celebron\source\social\interfaces\RequestInterface;
use Celebron\source\social\interfaces\SocialUserInterface;
use Celebron\source\social\responses\Id;
use yii\base\Arrayable;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;


/**
 * @property-read mixed $socialId
 * @property bool $active
 */
abstract class Social extends Component implements RequestInterface
{
    public const EVENT_SUCCESS = 'success';
    public const EVENT_FAILED = 'failed';
    public const EVENT_ERROR = 'error';

    protected readonly array|\ArrayAccess $params;

    public function __construct (
        public readonly string        $socialName,
        public readonly Configuration $configure,
                                      $config = [])
    {
        parent::__construct($config);
        if($this->configure->paramsHandler instanceof \ArrayAccess) {
            $this->params = $this->configure->paramsHandler;
        } elseif($this->configure->paramsHandler instanceof \Closure) {
            $this->params = $this->configure->paramsHandler->call($this->configure, $this->socialName);
        } elseif($this->configure->paramsGroup !== null) {
            $this->params = ArrayHelper::getValue(\Yii::$app->params, [$this->configure->paramsGroup, $this->socialName], []);
        }
    }

    private bool $_active = false;

    /**
     * @return bool
     */
    public function getActive (): bool
    {
        return $this->params['active'] ?? $this->_active;
    }
    public function setActive (bool $value): void
    {
        $this->_active = $value;
    }

    public function success (HandlerController $controller, Response $response): mixed
    {
       $event = new EventResult($controller, $response);
       $this->trigger(self::EVENT_SUCCESS, $event);
       return $this->result ?? $controller->goBack();
    }

    public function failed (HandlerController $controller, Response $response): mixed
    {
        $event = new EventResult($controller, $response);
        $this->trigger(self::EVENT_FAILED, $event);
        return $this->result ?? $controller->goBack();
    }

    public function responseId (string|\Closure|array|null $field, mixed $data): Id
    {
        return new Id($this, $field, $data);
    }

    public function url (string $action, string $state = null): string
    {
        return $this->configure->url($this->socialName, $action, $state);
    }

    public function getSocialId (): mixed
    {
        /** @var SocialUserInterface $user */
        $user = \Yii::$app->user->identity;
        $field = $user->getSocialField($this->socialName);
        return $user->$field;
    }

    public static function urlStatic(string $socialName, string $action, ?string $state = null, string $socialComponent = 'social'):string
    {
        return (new static($socialName,  \Yii::$app->get($socialComponent)))->url($action, $state);
    }

    public function __call ($name, $params)
    {
        $prefix = 'url';
        if(StringHelper::startsWith($name, $prefix)) {
            $actionName = strtolower(substr($name, strlen($prefix)));
            return $this->url($actionName, $params[0]??null);
        }
        return parent::__call($name, $params);
    }

    public function __toString ()
    {
        return $this->socialName;
    }
}