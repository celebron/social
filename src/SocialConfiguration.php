<?php

namespace Celebron\social;

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

    public string $register = 'register';

    public string $route = "social";

    public int $duration = 0;


    /** @var \Closure|null - событие отображение ошибок на все */
    public ?\Closure $onAllError = null;
    /** @var \Closure|null - cобытие регистрации на все */
    public ?\Closure $onAllRegisterSuccess = null;
    /** @var \Closure|null - cобытие автризации на все */
    public ?\Closure $onAllLoginSuccess = null;

    private array $_socials = [];



    /**
     * Получение списка активных соцсетей
     * @return Social[]
     */
    public function getSocials(): array
    {
        return $this->_socials;
    }

    /**
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
                    $object->on(Social::EVENT_REGISTER_SUCCESS, $this->onAllRegisterSuccess);
                }
                if($this->onAllLoginSuccess !== null){
                    $object->on(Social::EVENT_LOGIN_SUCCESS, $this->onAllLoginSuccess);
                }
                if($this->onAllError !== null) {
                    $object->on(Social::EVENT_ERROR, $this->onAllError);
                }
                $result[$key] = $object;
            } else {
                throw new NotSupportedException($class::class . ' does not extend ' . Social::class);
            }
        }
        $this->_socials = ArrayHelper::merge($this->_socials, $result);
    }


    /**
     * @return SocialConfiguration
     * @throws InvalidConfigException
     */
    public static function config() : static
    {
        return \Yii::$app->get(static::class);
    }


    /**
     * Получить ссылку на редеректа на соц.сеть
     * @param string $socialname
     * @param $register
     * @return string
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public static function link(string $socialname, bool|string $register = false): string
    {
        $config = self::config();
        $url[0] = $config->route . '/' . $socialname;
        if(is_bool($register) && $register) {
            $url['state'] = $config->register;
        }
        if(is_string($register)) {
            $url['state'] = $register;
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