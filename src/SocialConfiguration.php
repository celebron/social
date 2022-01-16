<?php

namespace Celebron\social;


use Celebron\social\socials\Yandex;
use yii\base\Component;

class SocialConfiguration extends Component
{
    public string $route = "site/social";

    private array $_socials = [];
    private array $_links = [];
    public function getSocials(): array
    {
        return $this->_socials;
    }
    public function getLinks(): array
    {
        return $this->_links;
    }

    public function setSocials(array $value): void
    {
        foreach ($value as $key=>$di) {
            if(is_numeric($key)) {
                $reflection = new \ReflectionClass($di['class']);
                $key = strtolower($reflection->getShortName());
            }
            $this->_socials[$key] = $di;
            $classname = $di['class'];
            $this->_links[$key] = $classname::Url();
        }
    }

    public function init ()
    {

    }

}