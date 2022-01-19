<?php

namespace Celebron\social;

use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class SocialAction extends \yii\base\Action
{
    public const ACTION_LOGIN = 'login';
    public const ACTION_REGISTER = 'register';

    public int $duration = 0;

    /**
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\HttpException
     * @throws BadRequestHttpException
     * @throws \yii\base\NotSupportedException
     */
    final public function run(string $state, ?string $code=null)
    {
        $explode = explode('_', $state, 2);
        $classname = $explode[0];
        $tag = $explode[1] ?? self::ACTION_LOGIN;

        $config = SocialConfiguration::config();
        if(!ArrayHelper::keyExists($classname, $config->socials))
        {
            throw new BadRequestHttpException();
        }


        /** @var SocialBase $class */
        $class = Instance::ensure($config->socials[$classname], SocialBase::class);
        $class->redirectUrl = Url::to([$this->controller->getRoute()],true);

        $class->validateCode($code, $state);

        if (($tag === self::ACTION_LOGIN) && $class->login()) {
            return $class->loginSuccess($this->controller);
        }

        //Режим регистрации
        if (($tag === self::ACTION_REGISTER) && $class->register()) {
            return $class->registerSuccess($this->controller);
        }

        return $class->error($this->controller);
    }

}