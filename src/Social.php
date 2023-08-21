<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social;

use Celebron\source\social\events\EventResult;
use Celebron\source\social\interfaces\CustomRequestInterface;
use Celebron\source\social\interfaces\RequestInterface;
use Celebron\source\social\interfaces\SocialUserInterface;
use Celebron\source\social\responses\Id;
use Celebron\source\social\responses\Response;
use Celebron\source\social\traits\ViewerBehavior;
use yii\base\Component;
use yii\base\InvalidConfigException;
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

    protected readonly \ReflectionClass $refThis;


    public readonly string $name;
    public function __construct (
        public readonly Configuration $configure,
        string|int|null $name = null,
        $config = [])
    {
        $this->refThis = new \ReflectionObject($this);

        //name взять из конфига если есть
        if($name === null && !empty($config['name'])) {
            $name = $config['name'];
            unset($config['name']);
        }

        //Окончательно устанавливаем name
        if($name === null || is_numeric($name)) {
            if($this->refThis->implementsInterface(CustomRequestInterface::class)) {
                throw new InvalidConfigException('Property $name is null or is numeric. Need alphabetic.');
            }
            $name = $this->refThis->getShortName();
        }

        $this->name = strtolower($name);

        //Добавляем внешние настройки
        if($this->configure->paramsHandler !== null) {
            $config = ArrayHelper::merge($config, $this->configure->paramsHandler->call($this->configure, $this->name, $config));
        }

        //Обработка $config
        foreach ($config as $key=>$value) {
            $propertyName = '_' . $key;
            $methodName = 'get' . $key;
            //readonly и write-config-only
            if( //readonly
                ($this->refThis->hasProperty($key) && ($refProperty = $this->refThis->getProperty($key))->isReadOnly())
                || //write-config-only
                ($this->refThis->hasMethod($methodName) && $this->refThis->hasProperty($propertyName)
                    && ($refProperty = $this->refThis->getProperty($propertyName))->isProtected()
                    && $this->refThis->getMethod($methodName)->isPublic()
                )
            ) {
                $refProperty->setValue($this, $value);
                unset($config[$key]);
            }
        }

        parent::__construct($config);
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
        return $this->configure->url($this->name, $action, $state);
    }

    public function getSocialId (): mixed
    {
        /** @var SocialUserInterface $user */
        $user = \Yii::$app->user->identity;
        $field = $user->getSocialField($this->name);
        return $user->$field;
    }

    public static function urlStatic(
        string $action,
        ?string $state = null,
        ?string $socialName = null,
        string $socialComponent = 'social'
    ):string
    {
        if($socialName === null) {
            $socialName = strtolower( (new \ReflectionClass(static::class))->getShortName() );
        }
        return Configuration::urlStatic($socialName, $action, $state, $socialComponent);
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
        return $this->getName();
    }


    /** @var bool - write-config-only */
    protected bool $_active = false;
    public function getActive (): bool
    {
        return $this->_active;
    }

    public function setActive(bool $value):void
    {
        throw new InvalidConfigException('Write ' . get_class($this) . '::$active configuration only');
    }
}