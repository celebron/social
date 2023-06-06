<?php

namespace Celebron\social;

use Celebron\social\args\RegisterEventArgs;
use Celebron\social\attrs\SocialName;
use yii\base\BootstrapInterface;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\UnknownMethodException;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Application;
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
    private array $_socials = [];
    public ?\Closure $onSuccess = null;
    public ?\Closure $onFailed = null;
    public ?\Closure $onError = null;

    public static self $config;

    public function __construct ($cfg = [])
    {
        self::$config = $this;
        parent::__construct($cfg);
    }

    /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
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
     * @throws \ReflectionException
     */
    public function add(array $socialClassConfig, mixed $socialName): void
    {
        if(is_numeric($socialName)) {
            $classRef = new \ReflectionClass($socialClassConfig['class']);
            $socialName = $classRef->getShortName();
            $attrs = $classRef->getAttributes(SocialName::class);
            if(isset($attrs[0])) {
                /** @var SocialName $attr */
                $attr = $attrs[0]->newInstance();
                $socialName = $attr->name;
            }
            $socialName = strtolower(trim($socialName));
        }

        if(ArrayHelper::keyExists($socialName, $this->_socials)) {
            throw new InvalidConfigException("Key $socialName exists");
        }

        $registerEventArgs = new RegisterEventArgs();
        $object = \Yii::createObject($socialClassConfig, [ $socialName, $this ]);

        $registerEventArgs->support = false;
        if($object instanceof AuthBase) {
            $registerEventArgs->social = $object;

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
            $this->_socials[$socialName] = $object;
        }
    }

    /**
     * @throws InvalidConfigException
     * @throws \ReflectionException
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

        if($object instanceof OAuth2 && \Yii::$app instanceof Application) {
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
            $name = substr($name, 6, strlen($name));
            return static::social($name, ...$arguments);
        }
        if(str_starts_with($name, 'url')) {
            $name = substr($name, 3, strlen($name));
            return static::url($name, $arguments[0], $arguments[1] ?? null);
        }
        throw new UnknownMethodException('Calling unknown method: ' . static::class . "::$name()");
    }
}