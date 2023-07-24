<?php

namespace Celebron\social;

use Celebron\social\events\EventRegister;
use Celebron\social\attrs\SocialName;
use Celebron\social\interfaces\CustomInterface;
use Celebron\social\interfaces\AuthInterface;
use Celebron\social\interfaces\OAuth2Interface;
use Celebron\social\interfaces\SocialAuthInterface;
use yii\base\BootstrapInterface;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\UnknownMethodException;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Application;


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

    public function addSocial(string $name, Component&SocialAuthInterface $object): void
    {
        if (ArrayHelper::keyExists($name, $this->_handlers)) {
            throw new InvalidConfigException("Key $name exists");
        }
        $eventRegister = new EventRegister($object);

        if ($this->onSuccess !== null) {
            $object->on(AuthBase::EVENT_SUCCESS, $this->onSuccess);
        }
        if ($this->onFailed !== null) {
            $object->on(AuthBase::EVENT_FAILED, $this->onFailed);
        }
        if ($this->onError !== null) {
            $object->on(AuthBase::EVENT_ERROR, $this->onError);
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
     */
    public function getSocial(string $name): ?SocialAuthInterface
    {
        $name =  strtolower(trim(strip_tags($name)));
        /** @var SocialAuthInterface $object */
        $object = ArrayHelper::getValue($this->getSocials(), $name);

        if($object === null) {
            return null;
        }

        if($object instanceof OAuth2Interface && \Yii::$app instanceof Application) {
            $object->setRedirectUrl(Url::toRoute([
                "{$this->route}/handler",
                'social' => $name,
            ], true));
        }

        return $object;
    }
}