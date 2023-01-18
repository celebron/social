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

    /** @var string - state для регистрации */
    public string $register = 'register';
    /** @var string - роут */
    public string $route = "social";
    /** @var int - на сколько сохранять участника */
    public int $duration = 0;
    /** @var \Closure|null - событие отображение ошибок на все */
    public ?\Closure $onAllError = null;
    /** @var \Closure|null - cобытие регистрации на все */
    public ?\Closure $onAllRegisterSuccess = null;
    /** @var \Closure|null - cобытие автризации на все */
    public ?\Closure $onAllLoginSuccess = null;
    /** @var \Closure|null - событие удаление на все */
    public ?\Closure $onAllDeleteSuccess = null;
    /** @var \Closure|null - событие поиск пользователя (алгоритм) */
    public ?\Closure $findUserAlg = null;

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
     * @return Social[]
     */
    public function getSocials (): array
    {
        return $this->_socials;
    }

    /**
     * Регистрация соцсетей
     * @param array $value
     * @throws InvalidConfigException
     * @throws NotSupportedException
     */
    public function setSocials (array $value): void
    {
        $this->trigger(self::EVENT_BEFORE_REGISTER);
        foreach ($value as $key => $class) {
            /** @var Social $object */
            $object = \Yii::createObject($class);
            if ($object instanceof Social) {
                //Регистрируем только активные классы
                if (!$object->active) {
                    continue;
                }

                //если ключ числовой, то переводим его в socialName
                if (is_numeric($key)) {
                    $key = strtolower($object::socialName());
                }

                //Установка обработчика всех успешных регистраций
                if ($this->onAllRegisterSuccess !== null) {
                    $object->on(Social::EVENT_REGISTER_SUCCESS, $this->onAllRegisterSuccess, ['config' => $this]);
                }

                //Установлка обработчика всех успешных авторизаций
                if ($this->onAllLoginSuccess !== null) {
                    $object->on(Social::EVENT_LOGIN_SUCCESS, $this->onAllLoginSuccess, ['config' => $this]);
                }

                //Установлка обработчика всех ошибок
                if ($this->onAllError !== null) {
                    $object->on(Social::EVENT_ERROR, $this->onAllError, ['config' => $this]);
                }

                //Установить обработчик на все успешные удаления (разных соцсетей)
                if($this->onAllDeleteSuccess !== null) {
                    $object->on(Social::EVENT_DELETE_SUCCESS, $this->onAllDeleteSuccess, ['config'=>$this]);
                }

                //Настройка алгоритма поиска пользователя
                if ($this->findUserAlg !== null) {
                    $object->on(Social::EVENT_FIND_USER, $this->findUserAlg, ['config' => $this]);
                }
                //Триггер непосредственной регистрации Social
                $registerEventArgs = new RegisterEventArgs($object);
                $this->trigger(self::EVENT_REGISTER, $registerEventArgs);

                $this->_socials[$key] = $object;
            } else {
                throw new NotSupportedException($class::class . ' does not extend ' . Social::class);
            }
        }
        $this->trigger(self::EVENT_AFTER_REGISTER);
    }

    /**
     * Получение данных по имени соц.сети
     * @param string $socialname - имя соц.сети (зарегистрированное имя)
     * @return Social
     * @throws NotFoundHttpException - ошибка, если соц.сеть не зарегистроирована
     * @throws \Exception - прочие ошибки
     */
    public function getSocial(string $socialname): Social
    {
        /** @var Social $object */
        $object = ArrayHelper::getValue($this->getSocials(), strtolower($socialname));

        if($object === null) {
            throw new NotFoundHttpException("Social '{$socialname}' not registered");
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
            "{$this->route}/<social>/delete" => "{$this->route}/delete",
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
     * @return Social
     * @throws NotFoundHttpException
     */
    public static function socialStatic(string $socialname) : Social
    {
        return  static::$config->getSocial($socialname);
    }

    /**
     * Вывод Socials[] (static)
     * @return Social[]
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
     * @throws Exception
     */
    public static function url (string $socialname, string $method = 'login', ?string $state=null): string
    {
        $url[0] = self::$config->route . '/' . strtolower($socialname);

        $random = \Yii::$app->security->generateRandomString();
        $data = [
          'method' => $method,
          'state' => $state,
          'random' => $random,
        ];

        $url['state'] = base64_encode(Json::encode($data));


        return Url::to($url, true);
    }
}