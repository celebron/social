<?php

namespace Celebron\social;

use Celebron\social\socials\Google;
use Celebron\social\socials\Vk;
use Celebron\social\socials\Yandex;
use ReflectionClass;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * Конфигуратор социальной авторизации
 * @property-read array $links - список линков зарегистрированных соцсетей
 * @property array $socials - зарегистрированые соцсети
 */
class SocialConfiguration extends Component
{
    /** @var string - стандартый роут */
    public string $route = "site/social";

    private array $_socials = [];

    /**
     * Получение списка активных соцсетей
     * @return array
     */
    public function getSocials(): array
    {
        return $this->_socials;
    }

    /**
     * Получение списка ссылок на автризацию
     * @param null $register
     * @return array
     * @throws InvalidConfigException
     */
    public function getLinks($register = null): array
    {
        $result = [];
        foreach ($this->getSocials() as $key=>$social) {
            $classname = $social['class'];
            /** @var Social $classname  */
            $result[$key] = $classname::url($register);
        }
        return $result;
    }

    /**
     * Регистрация соцсетей
     * @throws \ReflectionException
     */
    public function setSocials(array $value): void
    {
        $result= [];
        foreach ($value as $key=>$di)
        {
            if(is_numeric($key)) {
                $r = new ReflectionClass($di['class']);
                $key = strtolower($r->getShortName());
            }

            $result[$key] = $di;
        }
        $this->_socials = ArrayHelper::merge($this->_socials, $result);
    }


    /**
     * @return void
     * @throws \ReflectionException
     */
    public function init ()
    {
        $this->setSocials([
            [ 'class' => Yandex::class ],
            [ 'class' => Google::class ],
            [ 'class' => Vk::class ],
        ]);
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
    public static function ensure($socialname): Social
    {
        $config = static::config();
        $classArray = ArrayHelper::getValue($config->getSocials(), $socialname);
        if($classArray !== null) {
            $class =  \Yii::createObject($classArray);
            if($class instanceof Social) {
                return $class;
            }
            throw new NotSupportedException($class::class . ' does not extend ' . Social::class);
        }
        throw new NotFoundHttpException("Social {$socialname} not registered");
    }

}