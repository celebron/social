<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social;

use Celebron\source\social\traits\ViewerBehavior;
use Closure;
use Celebron\socials\{Google, Ok, VK, Yandex};
use Celebron\source\social\events\EventRegister;
use yii\base\BootstrapInterface;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\i18n\PhpMessageSource;

/**
 *
 * @property-write array $socials
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

    public ?Closure $paramsHandler = null;

    public array $eventToSocial = [
        //eventName => Closure
    ];

    public string $defaultIcon = '@public/icon.png';

    private array $_socials = [];

    /**
     * @throws InvalidConfigException
     */
    public function addSocialConfig(string|int $name, array|string $objectSetting):void
    {
        if(is_string($objectSetting)) {
            $objectSetting = [ 'class' => $objectSetting ];
        }

        $object = \Yii::createObject($objectSetting, [$this, $name]);
        /** @var Social $object */
        $object = Instance::ensure($object, Social::class);

        $eventRegister = new EventRegister($object);

        //Добавляем обработчики событий social (на глобальном уровне)
        foreach ($this->eventToSocial as $event => $closure) {
            $object->on($event, $closure);
        }

        $this->trigger(self::EVENT_REGISTER, $eventRegister);

        if ($eventRegister->support) {
            \Yii::info("Social '{$object->name}' registered", static::class);
            $this->_socials[$object->name] = $object;
        } else {
            \Yii::warning("Social '{$object->name}' not supported", static::class);
        }
    }


    /**
     * @throws InvalidConfigException
     */
    public function setSocials(array $socials):void
    {
        foreach ($socials as $name => $handler) {
            $this->addSocialConfig($name, $handler);
        }
    }

    /**
     * @param string|array|null $name
     * @param bool $interface
     * @return Social[]|Social|null
     * @throws \ReflectionException
     */
    public function get(string|array|null $name = null, bool $interface=false):array|Social|null
    {
        $filter = array_filter($this->_socials, function ($object, $key) use ($name, $interface) {
            //Если $name = null, то выводим все значения
            if ($name === null) {
                return true;
            }

            if (is_string($name)) {
                $name = [$name];
            }

            //Проверяем интерфейсы
            if ($interface) {
                $ref = new \ReflectionClass($object);
                return empty(array_diff($name, $ref->getInterfaceNames()));
            }

            //Проверяем ключи
            if(in_array($key, $name, true)) {
                return true;
            }

            return false;
        }, ARRAY_FILTER_USE_BOTH);

        if($name === null || $interface || is_array($name)) {
            return $filter;
        }

        $values = array_values($filter);
        return empty($values) ? null : $values[0];
    }


    public function has(string $name):bool
    {
        return ArrayHelper::keyExists($name, $this->_socials);
    }

    /**
     * @throws InvalidConfigException
     */
    public function bootstrap ($app)
    {
        $app->get('urlManager')
            ?->addRules([
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
        if($this->has($name)) {
            return $this->get($name);
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

    /**
     * @throws \ReflectionException
     * @throws InvalidConfigException
     */
    public static function getStatic(
        string|array|null $name = null,
        bool $interface=false,
        string|Configuration $componentName = 'social'
    ): Social|array|null
    {
        if(is_string($componentName)) {
            $component = Instance::ensure($componentName,self::class);
        }
        /** @var Configuration $component */
        return $component->get($name, $interface);
    }

    public static function urlStatic (
        string $social,
        string $action,
        ?state $state = null,
        string|Configuration $componentName = 'social'
    ): string
    {
        if(is_string($componentName)) {
            $component = Instance::ensure($componentName,self::class);
        }
        /** @var self $component */
        return $component->url($social, $action, $state);
    }
}