<?php

namespace Celebron\socialSource;

use Celebron\socialSource\behaviors\ActiveBehavior;
use Celebron\socialSource\behaviors\Behavior;
use Celebron\socialSource\events\EventRegister;
use Celebron\socialSource\interfaces\CustomRequestInterface;
use Celebron\socialSource\interfaces\SocialInterface;
use Celebron\socialSource\interfaces\ViewerInterface;
use yii\base\BootstrapInterface;
use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\base\UnknownMethodException;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use yii\helpers\Url;

/**
 *
 * @property-write array $socials
 * @method urlRegister(string $action, ?string $state = null):string
 */
class Configuration extends Component implements BootstrapInterface
{
    public const EVENT_REGISTER = 'register';

    public string $route = 'social';
    public ?string $paramsGroup = null;

    public array $socialEvents = [
        //eventName => Closure
    ];


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
        $object = \Yii::createObject($objectConfig, [ $name, $this ]);
        /** @var Request $object */
        $object = Instance::ensure($object, Request::class);
        $this->addSocial($name, $object);
    }

    /**
     * @throws InvalidConfigException
     */
    public function addSocial(string $name, Request $object, bool $override = false):void
    {
        if(empty($name)) {
            throw new InvalidArgumentException("Key $name empty");
        }

        //Проверяем на существования ключа (если переопределение невозможно)
        if (!$override && ArrayHelper::keyExists($name, $this->_socials)) {
            throw new InvalidArgumentException("Key $name exists");
        }

        $eventRegister = new EventRegister($object);

        //Добавляем обработчики событий
        foreach ($this->socialEvents as $event=>$closure) {
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
     * @return array|Request[]
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

    public function getSocial(string $name) : ?Request
    {
        return ArrayHelper::getValue($this->getSocials(), $name);
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
    }

    public function url (string $social, string $action, ?state $state = null): string
    {
        return Url::toRoute([
            $this->route . '/handler',
            'social' => $social,
            'state' => (string)State::create($action, $state),
        ]);
    }


    public static function __callStatic ($methodName, $arguments)
    {
        $prefix = 'url';
        $prefixLen = strlen($prefix);

        if(StringHelper::startsWith($methodName, $prefix)) {
            $name = strtolower(substr($methodName, $prefixLen));
            return static::$configure->url($name, $arguments[0], $arguments[1] ?? null);
        }
        throw new UnknownMethodException('Calling unknown method: ' . static::class . "::$methodName()");
    }
}