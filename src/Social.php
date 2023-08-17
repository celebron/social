<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social;

use Celebron\source\social\traits\SetterTrait;
use Celebron\source\social\traits\ViewerBehavior;
use Celebron\source\social\events\EventResult;
use Celebron\source\social\interfaces\RequestInterface;
use Celebron\source\social\interfaces\SocialUserInterface;
use Celebron\source\social\responses\Id;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;


/**
 * @property-read mixed $socialId
 * @property-write array $attributes
 * @property bool $active
 */
abstract class Social extends Component implements RequestInterface
{
    use SetterTrait;
    public const EVENT_SUCCESS = 'success';
    public const EVENT_FAILED = 'failed';
    public const EVENT_ERROR = 'error';

    public function __construct (
        public readonly string        $socialName,
        public readonly Configuration $configure,
                                      $config = [])
    {
        parent::__construct($config);
        $attrs = [];
        if($this->configure->paramsHandler instanceof \Closure) {
            $attrs = $this->configure->paramsHandler->call($this->configure, $this->socialName);
        } elseif($this->configure->paramsGroup !== null) {
            $attrs = ArrayHelper::getValue(\Yii::$app->params, [$this->configure->paramsGroup, $this->socialName], []);
        }
        $this->setAttributes($attrs);

    }

    public function setAttributes(array $values): void
    {
        foreach ($values as $key => $value) {
            $name = '_' . $key;
            if(property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
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


    protected bool $_active = false;
    public function getActive (): bool
    {
        return $this->_active;
    }
}