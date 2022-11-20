<?php

namespace Celebron\social;

use Celebron\social\eventArgs\FindUserEventArgs;
use yii\helpers\Url;
use yii\base\BootstrapInterface;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * Конфигуратор социальной авторизации
 * @property-read array $links - список линков зарегистрированных соцсетей
 * @property-read Social[] $socials - зарегистрированые соцсети
 * @property-write array $socials - регистрация классов
 */
class SocialConfiguration extends Component implements BootstrapInterface
{
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
    public function getSocials(): array
    {
        return $this->_socials;
    }

    /**
     * Список ссылок на соц.сети
     * @return array
     */
    #[\JetBrains\PhpStorm\ArrayShape(['name' => "string", 'login' => "string", 'register' => "string", 'icon' => "string"])]
    public function getLinks(): array
    {
        $links = [];

        foreach ($this->getSocials() as $key=>$social) {
            $links[$key] =[
                'name' => empty($social->name) ? $key : $social->name,
                'login' => $social::url(false),
                'register' => $social::url(true),
                'icon' => empty($object->icon) ? null : \Yii::getAlias($object->icon),
            ];
        }
        return $links;
    }

    /**
     * Регистрация соцсетей
     * @param array $value
     * @throws InvalidConfigException
     * @throws NotSupportedException
     */
    public function setSocials(array $value): void
    {
        foreach ($value as $key=>$class) {
            /** @var Social $object */
            $object = \Yii::createObject($class);
            if($object instanceof Social) {
                //Регистрируем только активные классы
                if(!$object->active) {
                    continue;
                }

                //если ключ числовой, то пееводим его в socialName
                if (is_numeric($key)) {
                    $key = strtolower($object::socialName());
                }

                //Установка обработчика всех успешных регистраций
                if($this->onAllRegisterSuccess !== null) {
                    $object->on(Social::EVENT_REGISTER_SUCCESS, $this->onAllRegisterSuccess, ['config'=> $this]);
                }

                //Установлка обработчика всех успешных авторизаций
                if($this->onAllLoginSuccess !== null){
                    $object->on(Social::EVENT_LOGIN_SUCCESS, $this->onAllLoginSuccess, ['config'=> $this]);
                }

                //Установлка обработчика всех ошибок
                if($this->onAllError !== null) {
                    $object->on(Social::EVENT_ERROR, $this->onAllError, ['config'=> $this]);
                }

                //Настройка алгоритма поиска пользователя
                if($this->findUserAlg === null) {
                    $object->on(Social::EVENT_FIND_USER, function(FindUserEventArgs $e) {
                        $e->defaultAlg();
                    },  ['config'=>$this]);
                } else {
                    $object->on(Social::EVENT_FIND_USER, $this->findUserAlg, ['config' => $this] );
                }

                $this->_socials[$key] = $object;
            } else {
                throw new NotSupportedException($class::class . ' does not extend ' . Social::class);
            }
        }

    }

    /**
     * Получить ссылку на редеректа на соц.сеть
     * @param string $socialname
     * @param bool|string $state
     * @return string
     */
    public static function link(string $socialname, bool|string $state = false): string
    {
        $url[0] = self::$config->route . '/' . strtolower($socialname);
        if(is_bool($state) && $state) {
            $url['state'] = self::$config->register;
        }
        if(is_string($state)) {
            $url['state'] = $state;
        }
        return Url::to($url, true);
    }

    /**
     * Инициализация механизма Bootstrap (Yii2)
     * @param $app - \Yii::$app
     * @return void
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
}