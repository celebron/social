<?php

namespace Celebron\social;

use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\helpers\Url;
use yii\web\HttpException;

class SocialAction extends Action
{
    public const ACTION_LOGIN = 'login';
    public const ACTION_REGISTER = 'register';

    public int $duration = 0;

    /**
     * @throws InvalidConfigException
     * @throws HttpException
     * @throws NotSupportedException
     */
    final public function run(string $state, ?string $code=null)
    {

        $explode = \explode('_',$state,2);
        $social = SocialConfiguration::ensure($explode[0]);
        $tag = $explode[1] ?? self::ACTION_LOGIN;

        $social->state = $state;
        $social->code = $code;
        $social->redirectUrl = Url::to([$this->controller->getRoute()],true);

        if (($tag === self::ACTION_LOGIN) && $social->login()) {
            return $social->loginSuccess($this->controller);
        }

        //Режим регистрации
        if (($tag === self::ACTION_REGISTER) && $social->register()) {
            return $social->registerSuccess($this->controller);
        }

        return $social->error($tag, $this->controller);
    }

}