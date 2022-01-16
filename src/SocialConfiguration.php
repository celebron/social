<?php

namespace Celebron\social;

use Celebron\social\socials\Yandex;
use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 *
 * @property-read array $links
 * @property array $socials
 */
class SocialConfiguration extends Component
{
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
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getLinks($register = false): array
    {
        $result = [];
        foreach ($this->getSocials() as $key=>$social) {
            $classname = $social['class'];
            /** @var SocialBase $classname  */
            $result[$key] = $classname::url($register);
        }
        return $result;
    }

    public function setSocials(array $value): void
    {
        $result= [];
        foreach ($value as $key=>$di)
        {
            if(is_numeric($key)) {
                $reflection = new \ReflectionClass($di['class']);
                $key = strtolower($reflection->getShortName());
            }
            $result[$key] = $di;
        }
        $this->_socials = ArrayHelper::merge($this->_socials, $result);
    }

    public function SocialAdd($class, $name=null)
    {
        $r = new \ReflectionClass($class);
        $name = $name ?? strtolower($r->getShortName());
        $this->setSocials([ $name => [ 'class'=> $class ] ]);
    }

    public function SocialAdd2(...$classes)
    {
        foreach ($classes as $class)
        {
            $this->socialAdd($class);
        }
    }

    public function init ()
    {
        $this->SocialAdd2(Yandex::class);
    }

}