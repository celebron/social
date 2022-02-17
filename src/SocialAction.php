<?php

namespace Celebron\social;

use yii\base\{Action, InvalidConfigException, NotSupportedException};
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
        \Yii::beginProfile("Social profiling", static::class);
        $explode = \explode('_',$state,2);
        $social = SocialConfiguration::ensure($explode[0]);
        $tag = $explode[1] ?? self::ACTION_LOGIN;

        $social->state = $state;
        $social->code = $code;
        $social->redirectUrl = Url::to([$this->controller->getRoute()],true);

        //Режим авторизации
        if (($tag === self::ACTION_LOGIN) && $social->login($this->duration)) {
            $result =  $social->loginSuccess($this->controller);
            \Yii::endProfile("Social profiling", static::class);
            return $result;
        }

        //Режим регистрации
        if (($tag === self::ACTION_REGISTER) && $social->register()) {
            $result = $social->registerSuccess($this->controller);
            \Yii::endProfile("Social profiling", static::class);
            return $result;
        }

        \Yii::endProfile("Social profiling", static::class);
        return $social->error($tag, $this->controller);
    }

}