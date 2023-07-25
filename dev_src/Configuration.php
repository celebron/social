<?php

namespace Celebron\social\dev;

use Celebron\social\dev\behaviors\ActiveBehavior;
use Celebron\social\dev\behaviors\OAuth2Behavior;
use Celebron\social\dev\behaviors\SocialViewBehavior;
use Celebron\social\dev\events\EventRegister;
use Celebron\social\dev\interfaces\CustomInterface;
use Celebron\social\dev\interfaces\OAuth2Interface;
use Celebron\social\dev\interfaces\SocialAuthInterface;
use Celebron\social\dev\interfaces\SocialViewInterface;
use yii\base\BootstrapInterface;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;


/**
 *
 * @property-write array $socials
 *
 * @method socialYandex
 * @method socialVk
 * @method socialTelegram
 * @method
 *
 */
class Configuration extends Component implements BootstrapInterface
{
    public const EVENT_BEFORE_REGISTER = 'beforeRegister';
    public const EVENT_AFTER_REGISTER = 'afterRegister';
    public const EVENT_REGISTER = 'register';

    public string $route = 'social';


    //### CLOSURES ###//
    public ?\Closure $onSuccess = null;
    public ?\Closure $onFailed = null;
    public ?\Closure $onError = null;

    public ?string $paramsGroup = null; // Группа в параметрах

    public array $behaviors = [
        SocialAuthInterface::class => ActiveBehavior::class,
        OAuth2Interface::class => OAuth2Behavior::class,
        SocialViewInterface::class => SocialViewBehavior::class,
    ];

    /** @var array|SocialAuthInterface[]  */
    private array $_handlers;

    /**
     * @inheritDoc
     */
    public function bootstrap ($app)
    {
        $app->urlManager->addRules([
            "{$this->route}/<social>" => "{$this->route}/handler",
        ]);

        $app->controllerMap[$this->route] = [
            'class' => SocialController::class,
            'config' => $this,
        ];
    }

    /**
     * @throws InvalidConfigException
     */
    public function addSocialConfig(string $name, array $objectConfig) : void
    {
        /** @var  Component&SocialAuthInterface $object */
        $object = \Yii::createObject($objectConfig, [ $name, $this ]);
        $this->addSocial($name, $object);
    }

    /**
     * @throws InvalidConfigException
     */
    public function addSocial(string $name, Component&SocialAuthInterface $object): void
    {
        if (ArrayHelper::keyExists($name, $this->_handlers)) {
            throw new InvalidConfigException("Key $name exists");
        }
        $eventRegister = new EventRegister($object);

        if ($this->onSuccess !== null) {
            $object->on(SocialAuthInterface::EVENT_SUCCESS, $this->onSuccess);
        }
        if ($this->onFailed !== null) {
            $object->on(SocialAuthInterface::EVENT_FAILED, $this->onFailed);
        }
        if ($this->onError !== null) {
            $object->on(SocialAuthInterface::EVENT_ERROR, $this->onError);
        }

        $classRef = new \ReflectionClass($object);
        foreach ($this->behaviors as $interface => $behavior) {
            if(class_exists($behavior) && $classRef->implementsInterface($interface)) {
                $object->attachBehavior($interface, \Yii::createObject($behavior, [ $name, $this ]));
            }
        }

        $this->trigger(self::EVENT_REGISTER, $eventRegister);

        if (!$eventRegister->support) {
            \Yii::warning($object::class . ' not support', static::class);
        } else {
            \Yii::info("$name registered...", static::class);
            $this->_handlers[$name] = $object;
        }
    }

    /**
     * @throws \ReflectionException
     * @throws InvalidConfigException
     */
    public function setSocials(array $handlers):void
    {
        $this->trigger(self::EVENT_BEFORE_REGISTER);
        foreach ($handlers as $name => $handler) {
            if (is_numeric($name)) {
                if (is_string($handler)) {
                    $className = $handler;
                } elseif (is_array($handler) && isset($handler['class'])) {
                    $className = $handler['class'];
                } else {
                    throw new InvalidConfigException();
                }
                $classRef = new \ReflectionClass($className);
                if ($classRef->implementsInterface(CustomInterface::class)) {
                    throw new InvalidConfigException("Key is numeric. The key must be alphabetical.");
                }
                $name = strtolower($classRef->getShortName());
            }
            $this->addSocialConfig($name, $handler);
        }
        $this->trigger(self::EVENT_AFTER_REGISTER);
    }

    /**
     * @throws \ReflectionException
     */
    public function getSocials(...$interfaces):array
    {
        if(count($interfaces) > 0) {
            $result = [];
            foreach ($this->_handlers as $social) {
                $classRef = new \ReflectionClass($social);
                if(count(array_intersect($classRef->getInterfaceNames(), $interfaces)) > 0) {
                    $result[] = $social;
                }
            }
            return $result;
        }
        return $this->_handlers;
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function getSocial(string $name): ?SocialAuthInterface
    {
        $name =  strtolower(trim(strip_tags($name)));
        /** @var SocialAuthInterface $object */
        $object = ArrayHelper::getValue($this->getSocials(), $name);
        return $object;
    }

}