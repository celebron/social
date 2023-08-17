<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social;

use Celebron\source\social\traits\ViewerBehavior;
use Celebron\source\social\interfaces\ViewerInterface;
use Celebron\socials\{Google, Ok, VK, Yandex};
use Celebron\source\social\events\EventRegister;
use Celebron\source\social\interfaces\CustomRequestInterface;
use yii\base\BootstrapInterface;
use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\base\UnknownMethodException;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\i18n\PhpMessageSource;

/**
 *
 * @property array $socials
 * @property-read  Yandex $yandex
 * @property-read  VK $vk
 * @property-read Ok $ok
 * @property-read Google $google
 *
 * @example
 *      <social>Static() ==> static::$configure->getSocial(<social>)
 *      <social>Url(...) ==> static::$configure->url(<social>,...)
 *      url<Social>(...) ==> $this->url(<social>,...)
 *
 */
class Configuration extends Component implements BootstrapInterface
{
    public const EVENT_REGISTER = 'register';

    public string $route = 'social';
    public ?string $paramsGroup = null;
    public null|\Closure $paramsHandler = null;

    public array $eventToSocial = [
        //eventName => Closure
    ];

    public string $defaultIcon = '@public/icon.png';

    private array $_socials = [];
    private static self $configure;

    public function __construct ($config = [])
    {
        parent::__construct($config);
        self::$configure = $this;
    }

    /**
     * @throws InvalidConfigException
     */
    public function addSocialConfig(string $name, array|string $objectConfig):void
    {
        $object = \Yii::createObject($objectConfig, [$name, $this]);
        /** @var Social $object */
        $object = Instance::ensure($object, Social::class);
        $this->addSocial($name, $object);
    }

    /**
     */
    public function addSocial (string $name, Social $object, bool $override = false): void
    {
        if (empty($name)) {
            throw new InvalidArgumentException("Key $name empty");
        }

        //Проверяем на существования ключа (если переопределение невозможно)
        if (!$override && ArrayHelper::keyExists($name, $this->_socials)) {
            throw new InvalidArgumentException("Key $name exists");
        }

        $eventRegister = new EventRegister($object);

        //Добавляем обработчики событий
        foreach ($this->eventToSocial as $event=> $closure) {
            $object->on($event, $closure);
        }

        $this->trigger(self::EVENT_REGISTER, $eventRegister);

        if ($eventRegister->support) {
            \Yii::info("Social '$name' registered", static::class);
            $this->_socials[$name] = $object;
        } else {
            \Yii::warning("Social '$name' not supported", static::class);
        }

    }

    /**
     * @param mixed ...$interfaces
     * @return array|Social[]
     * @throws \ReflectionException
     */
    public function getSocials (mixed ...$interfaces): array
    {
        if (count($interfaces) > 0) {
            $result = [];
            foreach ($this->_socials as $key => $social) {
                $classRef = new \ReflectionClass($social);
                if (count(array_intersect($classRef->getInterfaceNames(), $interfaces)) > 0) {
                    $result[$key] = $social;
                }
            }
            return $result;
        }
        return $this->_socials;
    }

    public function setSocials(array $socials):void
    {
        foreach ($socials as $name => $handler) {
            if(is_numeric($name)) {
                if(is_string($handler)) {
                    $className = $handler;
                } elseif(is_array($handler) && isset($handler['class'])) {
                    $className = $handler['class'];
                } else {
                    throw new InvalidConfigException();
                }
                $classRef = new \ReflectionClass($className);
                if($classRef->implementsInterface(CustomRequestInterface::class)) {
                    throw new InvalidConfigException("Key is numeric. The key must be alphabetical.");
                }
                $name = strtolower($classRef->getShortName());
            }
            $this->addSocialConfig($name, $handler);
        }
    }

    public function getSocial (string $name): Social|OAuth2|null
    {
        return ArrayHelper::getValue($this->getSocials(), $name);
    }

    public function hasSocial(string $name):bool
    {
        return ArrayHelper::keyExists($name, $this->getSocials());
    }

    public function bootstrap ($app)
    {
        $app->urlManager->addRules([
            "{$this->route}/<social>" => "{$this->route}/handler",
        ]);

        $app->controllerMap[$this->route] = [
            'class' => HandlerController::class,
            'configure' => $this,
        ];

        $app->i18n->translations['social*'] = [
            'class' => PhpMessageSource::class,
            'sourceLanguage' => 'en-US',
            'basePath' => '@Celebron/source/social/messages',
        ];
    }

    public function url (string $social, string $action, ?state $state = null): string
    {
        return Url::toRoute([
            $this->route . '/handler',
            'social' => $social,
            'state' => (string)State::create($action, $state),
        ]);
    }

    public function __get ($name)
    {
        if($this->hasSocial($name)) {
            return $this->getSocial($name);
        }
        return parent::__get($name);
    }

    public function __call ($name, $params)
    {
        //URLS
        $prefix = 'url';
        if(StringHelper::startsWith($name, $prefix)) {
            $socialName = strtolower(substr($name, strlen($prefix)));
            return $this->url($socialName, $params[0], $params[1]?? null);
        }
        return parent::__call($name, $params);
    }

    public static function __callStatic ($methodName, $arguments)
    {
        ///URLS
        $suffix = 'Url';
        if(StringHelper::endsWith($methodName, $suffix)) {
            $name = strtolower(substr($methodName,0, -strlen($suffix)));
            return static::$configure->url($name, $arguments[0], $arguments[1] ?? null);
        }

        ///SOCIALS
        $suffix = 'Static';
        if(StringHelper::endsWith($methodName, $suffix)) {
            $name = strtolower(substr($methodName,0, -strlen($suffix)));
            if($name === 'socials') {
                return static::$configure->getSocials(...$arguments);
            }
            return static::$configure->getSocial($name);
        }

        throw new UnknownMethodException('Calling unknown method: ' . static::class . "::$methodName()");
    }
}