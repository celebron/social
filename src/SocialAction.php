<?php

namespace Celebron\social;

use yii\base\{Action, InvalidConfigException, NotSupportedException};
use yii\helpers\Url;
use yii\web\HttpException;

class SocialAction extends Action
{
    public int $duration = 0;

    /**
     * @throws InvalidConfigException
     * @throws HttpException
     * @throws NotSupportedException
     */
    final public function run(string $social, ?string $state =null, ?string $code=null)
    {
        $register = ($state !== null) && str_contains(SocialConfiguration::config()->register, $state);


        \Yii::beginProfile("Social profiling", static::class);
        $socialObject = SocialConfiguration::ensure($social);

        $socialObject->state = $state;
        $socialObject->code = $code;
        $socialObject->redirectUrl = Url::to([$this->controller->getRoute()],true);

        if($register && $socialObject->register()) {
            $result = $socialObject->registerSuccess($this);
            \Yii::endProfile("Social profiling", static::class);
            return $result;
        }

        if(!$register && $socialObject->login($this->duration)) {
            $result =  $socialObject->loginSuccess($this);
            \Yii::endProfile("Social profiling", static::class);
            return $result;
        }

        \Yii::endProfile("Social profiling", static::class);
        return $socialObject->error($this);
    }

}