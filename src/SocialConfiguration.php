<?php

namespace Celebron\social;


use Celebron\social\socials\Yandex;
use yii\base\Component;

class SocialConfiguration extends Component
{
    public string $route = "site/social";

    public array $socials = [
        'yandex' => [
            'class' => Yandex::class,
            'clientUrl' => "https://oauth.yandex.ru",
        ],
    ];
}