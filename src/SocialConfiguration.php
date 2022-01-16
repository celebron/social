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

    private array $_socials = [
        [ 'class' => Yandex::class ]
    ];

    public function getSocials(): array
    {
        $result = [];
        foreach ($this->_socials as $key=>$di) {
            if(is_numeric($key)) {
                $reflection = new \ReflectionClass($di['class']);
                $key = strtolower($reflection->getShortName());
            }
            $result[$key] = $di;
        }
        return $result;
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getLinks(): array
    {
        $result = [];
        foreach ($this->getSocials() as $key=>$social) {
            $classname = $social['class'];
            /** @var SocialBase $classname  */
            $result[$key] = $classname::url();
        }
        return $result;
    }

    public function setSocials(array $value): void
    {
        $this->_socials = ArrayHelper::merge($this->_socials, $value);
    }

    public function init ()
    {

    }

}