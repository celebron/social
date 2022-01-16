<?php

namespace Celebron\social;


use Celebron\social\socials\Yandex;
use yii\base\Component;

/**
 *
 * @property-read array $links
 * @property array $socials
 */
class SocialConfiguration extends Component
{
    public string $route = "site/social";

    private array $_socials = [];
    private array $_links = [];
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
    public function getLinks(): array
    {
        return $this->_links;
    }

    public function setSocials(array $value): void
    {
        $this->_socials = $value;
    }

    public function init ()
    {

    }

}