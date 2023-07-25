<?php

namespace Celebron\social;

use yii\base\BootstrapInterface;
use yii\base\Component;

class Configuration extends Component implements BootstrapInterface
{
    public string $route = 'social';

    private array $_socials = [];

    public function addSocialConfig($name, Component&HandlerInterface $handler)
    {

    }

    /**
     * @inheritDoc
     */
    public function bootstrap ($app)
    {
        $app->urlManager->addRules([
            "{$this->route}/<social>" => "{$this->route}/handler",
        ]);

        $app->controllerMap[$this->route] = [
            'class' => HandlerController::class,
            'configure' => $this,
        ];
    }
}