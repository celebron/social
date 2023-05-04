<?php

namespace Celebron\social;

use Celebron\social\eventArgs\RegisterEventArgs;
use yii\base\Exception;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\base\BootstrapInterface;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * Конфигуратор социальной авторизации
 * @property-read Social[] $socials - зарегистрированые соцсети
 //* @property-write array $socials - регистрация классов
 */
class SocialConfiguration extends Component implements BootstrapInterface
{
    public const EVENT_BEFORE_REGISTER = 'beforeRegister';
    public const EVENT_AFTER_REGISTER = 'afterRegister';
    public const EVENT_REGISTER = 'register';

    /** @var self - конфигурация */
    public static self $config;

    /** @var string - роут */
    public string $route = "social";
    /** @var int - на сколько сохранять участника */
    public int $duration = 0;
    /** @var \Closure|null - событие отображение ошибок на все */
    public ?\Closure $onError = null;

    /** @var \Closure|null - cобытие выполнено */
    public ?\Closure $onSuccess = null;

    /** @var \Closure|null - событие провал */
    public ?\Closure $onFailed = null;

    /** @var \Closure|null - cобытие автризации на все */
    public ?\Closure $findUserAlg = null;



    /** @var OAuth2[]|social[] array  */
    private array $_socials = [];


    /**
     * Инициализация класса (стандарт Yii2)
     * @return void
     */
    public function init ()
    {
        self::$config = $this;
    }

    /**
     * Получение списка активных соцсетей
     * @return Social[]|OAuth2[]
     */
    public function getSocials (): array
    {
        $args = func_get_args();
        if(func_num_args() > 0) {
            $result = [];
            foreach ($this->_socials as $social) {
                $classRef = new \ReflectionClass($social);
                if(count(array_intersect($classRef->getInterfaceNames(), $args)) > 0) {
                    $result[] = $social;
                }
            }

            return $result;
        }
        return $this->_socials;
    }


    /**
     * Регистрация соцсетей
     * @param array $value
     * @throws InvalidConfigException

     */
    public function setSocials (array $value): void
    {
        $this->trigger(self::EVENT_BEFORE_REGISTER);
        $i = 0;
        foreach ($value as $key => $class) {
            /** @var Social $object */
            $object = \Yii::createObject($class);
            $registerEventArgs = new RegisterEventArgs($object);

            if($object instanceof AuthBase) {
                //Регистрируем только активные классы
                if (!$object->active) {
                    continue;
                }

                //если ключ числовой, то переводим его в socialName
                if (is_numeric($key)) {
                    $key = strtolower($object::socialName());
                    //Если ключ существует, то добавляем числовой суфикс 0,1 и тд
                    if(ArrayHelper::keyExists($key, $this->_socials)) {
                        $key .= $i++;
                    }
                }

                //Установка обработчика удачных выполнений
                if($this->onSuccess !== null) {
                    $object->on(AuthBase::EVENT_SUCCESS, $this->onSuccess, [ 'config' => $this ]);
                }

                //Установка обработчика неудачных выполнений
                if($this->onFailed !== null) {
                    $object->on(AuthBase::EVENT_FAILED, $this->onFailed, [ 'config', $this ] );
                }

                //Установка обработчика всех ошибок
                if ($this->onError !== null) {
                    $object->on(AuthBase::EVENT_ERROR, $this->onError, ['config' => $this]);
                }

                $registerEventArgs->support = true;
            }

            if ($object instanceof Social) {
                //Настройка алгоритма поиска пользователя
                if ($this->findUserAlg !== null) {
                    $object->on(Social::EVENT_FIND_USER, $this->findUserAlg, ['config' => $this]);
                }
            }

            //Триггер непосрественной регистрации
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

    /**
     * Получение данных по имени соц.сети
     * @param string $social - имя соц.сети (зарегистрированное имя)
     * @return AuthBase|null
     * @throws \Exception - прочие ошибки
     */
    public function getSocial(string $social): ?AuthBase
    {
        $social =  strtolower(trim($social));
        /** @var AuthBase $object */
        $object = ArrayHelper::getValue($this->getSocials(), $social);

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

    /**
     * Инициализация механизма Bootstrap (Yii2)
     * @param $app - \Yii::$app
     * @return void
     */
    public function bootstrap ($app): void
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
     * Выводит Social класс по имени класса (static)
     * @param string $socialname
     * @return null|AuthBase
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public static function socialStatic(string $socialname) : ?AuthBase
    {
        return  static::$config->getSocial($socialname);
    }

    /**
     * Вывод Socials[] (static)
     * @return AuthBase[]
     */
    public static function socialsStatic(): array
    {
        return static::$config->getSocials();
    }

    /**
     * Получить ссылку на редеректа на соц.сеть
     * @param string $socialname - социальная сеть (ключ из конфига0
     * @param string $method - (login|register|delete)
     * @param string|null $state - дополнительные данные
     * @return string
     */
    public static function url (string $socialname, string $method, ?string $state=null): string
    {
        $url[0] = self::$config->route . '/handler';
        $url['social'] = strtolower(trim($socialname));
        $url['state'] = (string)State::create($method, $state);
        return Url::toRoute($url, true);
    }

}