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
 * @property Social[] $socials - зарегистрированые соцсети
 */
class SocialConfiguration extends Component implements BootstrapInterface
{
    /** @var $this - конфигурация */
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

    public ?\Closure $findUserAlg = null;

    private array $_socials = [];


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
     * @param $register
     * @return array
     * @throws InvalidConfigException
     */
    public function getLinks(bool $register = false): array
    {
        $result = [];
        foreach ($this->getSocials() as $key=>$social) {
            if(!$social->active) {
                continue;
            }
            $result[$key] = [
                'name' => empty($social->name) ? $key : $social->name,
                'link' => $social::url($register),
                'icon' => empty($social->icon) ? null : $social->icon,
            ];
        }
        return $result;
    }

    /**
     * Регистрация соцсетей
     * @param array $value
     * @throws InvalidConfigException
     * @throws NotSupportedException
     */
    public function setSocials(array $value): void
    {
        $result= [];
        foreach ($value as $key=>$class) {
            /** @var Social $object */
            $object = \Yii::createObject($class);
            if($object instanceof Social) {
                if (is_numeric($key)) {
                    $key = strtolower($object::socialName());
                }
                if($this->onAllRegisterSuccess !== null) {
                    $object->on(Social::EVENT_REGISTER_SUCCESS, $this->onAllRegisterSuccess, ['config'=> $this]);
                }
                if($this->onAllLoginSuccess !== null){
                    $object->on(Social::EVENT_LOGIN_SUCCESS, $this->onAllLoginSuccess, ['config'=> $this]);
                }
                if($this->onAllError !== null) {
                    $object->on(Social::EVENT_ERROR, $this->onAllError, ['config'=> $this]);
                }

                if($this->findUserAlg === null) {
                    $object->on(Social::EVENT_FIND_USER, function(FindUserEventArgs $e) {
                        /** @var Social $sender */
                        $sender = $e->sender;
                        if($sender->id !== null) {
                            $e->user = $e->userQuery->andWhere([$sender->field => $sender->id])->one();
                        }
                    },  ['config'=>$this ]);
                } else {
                    $object->on(Social::EVENT_FIND_USER, $this->findUserAlg, ['config'=> $this] );
                }

                $result[$key] = $object;
            } else {
                throw new NotSupportedException($class::class . ' does not extend ' . Social::class);
            }
        }
        $this->_socials = ArrayHelper::merge($this->_socials, $result);
    }

    /**
     * Получить ссылку на редеректа на соц.сеть
     * @param string $socialname
     * @param $state
     * @return string
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
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