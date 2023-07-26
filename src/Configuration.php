<?php

namespace Celebron\socialSource;

use Celebron\socialSource\events\EventRegister;
use Celebron\socialSource\interfaces\SocialRequestInterface;
use yii\base\BootstrapInterface;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

class Configuration extends Component implements BootstrapInterface
{
    public const EVENT_REGISTER = 'register';

    public string $route = 'social';
    public array $behaviors = [

    ];

    public array $events = [
        //eventName => Closure
    ];

    private array $_socials = [];

    /**
     * @throws InvalidConfigException
     */
    public function addSocialConfig(string $name, array $objectConfig):void
    {
        /** @var Component&SocialRequestInterface $object */
        $object = \Yii::createObject($objectConfig, [ $name, $this ]);
        $this->addSocial($name, $object);
    }

    public function addSocial(string $name, Component&SocialRequestInterface $object, bool $override = false):void
    {
        //Проверяем на существования ключа (если переопределение невозможно)
        if (!$override && ArrayHelper::keyExists($name, $this->_socials)) {
            throw new InvalidConfigException("Key $name exists");
        }

        $eventRegister = new EventRegister($object);

        $classRef = new \ReflectionClass($object);

        //Добавляем behaviors
        foreach ($this->behaviors as $interface => $behavior) {
            if(class_exists($behavior) && $classRef->implementsInterface($interface)) {
                $object->attachBehavior($interface, $behavior);
            }
        }

        //Добавляем обработчики событий
        foreach ($this->events as $event=>$closure) {
            $object->on($event, $closure);
        }

        $this->trigger(self::EVENT_REGISTER, $eventRegister);

        if($eventRegister->support) {
            \Yii::info("Social '$name' registered", static::class);
            $this->_socials[$name] = $object;
        } else {
            \Yii::warning("Social '$name' not supported", static::class);
        }

    }



    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
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
}