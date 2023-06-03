<?php

namespace Celebron\social;

use Celebron\social\args\RegisterEventArgs;
use yii\base\BaseObject;
use yii\base\BootstrapInterface;
use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\base\UnknownMethodException;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

/**
 *
 * @property-write array $socials
 */
class SocialConfiguration extends Component implements BootstrapInterface
{
    public const EVENT_BEFORE_REGISTER = 'beforeRegister';
    public const EVENT_AFTER_REGISTER = 'afterRegister';
    public const EVENT_REGISTER = 'register';

    public string $route = "social";
    public string $prefixMethod = 'social';

    public ?string $adminHandler = null;

    private array $_socials = [];
    public ?\Closure $onSuccess = null;
    public ?\Closure $onFailed = null;
    public ?\Closure $onError = null;

    private static self $config;

    public function init ()
    {
        self::$config = $this;
    }

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
    public function add(array|callable|string $socialClass, $key = null): void
    {
        $registerEventArgs = new RegisterEventArgs();
        $object = \Yii::createObject($socialClass, [ $this ]);
        $registerEventArgs->support = false;

        if($object instanceof AuthBase) {
            $registerEventArgs->social = $object;

            if(is_numeric($key) || $key === null) {
                $key = strtolower($object::socialName());
                if(ArrayHelper::keyExists($key, $this->_socials)) {
                    throw new InvalidConfigException("Key $key exists");
                }
            }

            if($this->onSuccess !== null) {
                $object->on(AuthBase::EVENT_SUCCESS, $this->onSuccess);
            }

            if($this->onFailed !== null) {
                $object->on(AuthBase::EVENT_FAILED, $this->onFailed);
            }

            if($this->onError !== null) {
                $object->on(AuthBase::EVENT_ERROR, $this->onError);
            }

            $registerEventArgs->support = true;
        }

        $this->trigger(self::EVENT_REGISTER, $registerEventArgs);

        if(!$registerEventArgs->support) {
            \Yii::warning($object::class . ' not support',static::class);
        }

        if($registerEventArgs->support && $object?->active) {
            $this->_socials[$key] = $object;
        }
    }

    /**
     * @throws InvalidConfigException
     */
    public function setSocials(array $socials):void
    {
        $this->trigger(self::EVENT_BEFORE_REGISTER);
        foreach ($socials as $key => $class) {
            $this->add($class, $key);
        }
        $this->trigger(self::EVENT_AFTER_REGISTER);
    }

    /**
     * @throws \ReflectionException
     */
    public function getSocials (...$interfaces): array
    {
        if(count($interfaces) > 0) {
            $result = [];
            foreach ($this->_socials as $social) {
                $classRef = new \ReflectionClass($social);
                if(count(array_intersect($classRef->getInterfaceNames(), $interfaces)) > 0) {
                    $result[] = $social;
                }
            }
            return $result;
        }
        return $this->_socials;
    }

    /**
     * @throws \Exception
     */
    public function get(string $social, ...$interface): ?AuthBase
    {
        $social =  strtolower(trim(strip_tags($social)));
        /** @var AuthBase $object */
        $object = ArrayHelper::getValue($this->getSocials(...$interface), $social);

        if($object === null) {
            return null;
        }

        if($object instanceof OAuth2) {
            $object->redirectUrl = Url::toRoute([
                "{$this->route}/handler",
                'social' => $social,
            ], true);
        }

        return $object;
    }

    public static function url (string $socialName, string $method, ?string $state=null): string
    {
        $url[0] = self::$config->route . '/handler';
        $url['social'] = strtolower(trim($socialName));
        $url['state'] = (string)State::create($method, $state);
        return Url::toRoute($url, true);
    }

    /**
     * Выводит Social класс по имени класса (static)
     * @param string $socialName
     * @param mixed ...$interfaces
     * @return null|AuthBase
     * @throws \Exception
     */
    public static function social(string $socialName, ...$interfaces) : ?AuthBase
    {
        return  static::$config->get($socialName, ...$interfaces);
    }

    /**
     * Вывод Socials[] (static)
     * @return AuthBase[]
     * @throws \ReflectionException
     */
    public static function socials(...$interfaces): array
    {
        return static::$config->getSocials(...$interfaces);
    }

    /**
     * @throws \Exception
     */
    public static function __callStatic ($name, $arguments)
    {
        if(str_starts_with($name, 'social')) {
            $name = str_replace('social','', $name);
            return static::social($name, ...$arguments);
        }
        if(str_starts_with($name, 'url')) {
            $name = str_replace('url','', $name);
            return static::url($name, $arguments[0], isset($arguments[1])?:null);
        }
        throw new UnknownMethodException('Calling unknown method: ' . static::class . "::$name()");
    }
}