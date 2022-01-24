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
 * @property Social[] $socials - зарегистрированые соцсети
 */
class SocialConfiguration extends Component
{
    /** @var string - стандартый роут */
    public string $route = "site/social";

    public function setOnAllOnError(\Closure $closure)
    {
        foreach ($this->getSocials() as $social) {
            $social->on(Social::EVENT_ERROR, $closure);
        }
    }

    public function setOnAllRegisterSuccess(\Closure $closure)
    {
        foreach ($this->getSocials() as $social) {
            $social->on(Social::EVENT_REGISTER_SUCCESS, $closure);
        }
    }

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
            $activate = $social['active'] ?? false;
            if(!$activate) {
                continue;
            }
            /** @var Social $classname  */
            $result[$key] = $classname::url($register);
        }
        return $result;
    }

    /**
     * Регистрация соцсетей
     * @throws \ReflectionException
     * @throws InvalidConfigException
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
                $result[$key] = $object;
            } else {
                throw new NotSupportedException($class::class . ' does not extend ' . Social::class);
            }
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
            [ 'class' => Yandex::class, 'active' => true, ],
            [ 'class' => Google::class, 'active' => true, ],
            [ 'class' => Vk::class, 'active' => true, ],
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
        $object = ArrayHelper::getValue($config->getSocials(), $socialname);
        if($object !== null) {
            return $object;
        }
        throw new NotFoundHttpException("Social {$socialname} not registered");
    }

}