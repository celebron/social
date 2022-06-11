<?php

namespace Celebron\social;

use JetBrains\PhpStorm\ArrayShape;
use Yii;
use yii\base\Application;
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
    public function getLinks($register = null): array
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
        foreach ($value as $key=>$class)
        {
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
        return Yii::$app->get(static::class);
    }

    /**
     * @param $socialname
     * @return Social
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public static function ensure(string $socialname): Social
    {
        $config = static::config();
        $object = ArrayHelper::getValue($config->getSocials(), $socialname);
        if($object !== null) {
            return $object;
        }
        throw new NotFoundHttpException("Social {$socialname} not registered");
    }

    /**
     * Получить ссылку на редеректа на соц.сеть
     * @param string $socialname
     * @param $register
     * @return string
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public static function link(string $socialname, $register = null): string
    {
        return self::ensure($socialname)::url($register);
    }

    public function bootstrap ($app)
    {
        $app->controllerMap['social'] = [
            'class' => SocialController::class,
            'config' => $this,
        ];

        $app->urlManager->rules['social/<social>'] = "social/handler";
    }
}