<?php

namespace Celebron\social;

use Celebron\social\args\RegisterEventArgs;
use yii\base\BootstrapInterface;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

class SocialConfiguration extends Component implements BootstrapInterface
{
    public const EVENT_BEFORE_REGISTER = 'beforeRegister';
    public const EVENT_AFTER_REGISTER = 'afterRegister';
    public const EVENT_REGISTER = 'register';

    public string $route = "social";
    public string $prefixMethod = 'social';

    private array $_socials = [];
    public ?\Closure $onSuccess;
    public ?\Closure $onFailed;
    public ?\Closure $onError;

    public static self $config;

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

    public function setSocials(array $socials):void
    {
        $this->trigger(self::EVENT_BEFORE_REGISTER);
        $i = 0;
        foreach ($socials as $key => $class) {
            $object = \Yii::createObject($class, [ $this ]);
            $registerEventArgs = new RegisterEventArgs($object);
            $registerEventArgs->support = false;

            if($object instanceof AuthBase) {
                if(!$object->active) {
                    continue;
                }

                if(is_numeric($key)) {
                    $key = strtolower($object::socialName());
                    if(ArrayHelper::keyExists($key, $this->_socials)) {
                        $key .= $i++;
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

            //Триггер непосредственной регистрации
            $this->trigger(self::EVENT_REGISTER, $registerEventArgs);

            //Не регистрировать, если не поддерживается
            if(!$registerEventArgs->support) {
                \Yii::warning($object::class . ' not support',static::class);
                continue;
            }

            $this->_socials[$key] = $object;
        }
        $this->trigger(self::EVENT_AFTER_REGISTER);
    }

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
     * @return null|AuthBase
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public static function social(string $socialName, ...$interfaces) : ?AuthBase
    {
        return  static::$config->get($socialName, ...$interfaces);
    }

    /**
     * Вывод Socials[] (static)
     * @return AuthBase[]
     */
    public static function socials(...$interfaces): array
    {
        return static::$config->getSocials(...$interfaces);
    }
}