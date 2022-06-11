<?php

namespace Celebron\social;

use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

class SocialController extends \yii\web\Controller
{
    public SocialConfiguration $config;

    /**
     * @throws \yii\base\NotSupportedException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\UnauthorizedHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionHandler(string $social, ?string $code = null, ?string $state = null)
    {
        $register = ($state !== null) && str_contains($this->config->register, $state);

        \Yii::beginProfile("Social profiling", static::class);
        $socialObject = ArrayHelper::getValue($this->config->getSocials(), $social);
        if($socialObject === null ) {
            throw new NotFoundHttpException("Social {$social} not registered");
        }

        $socialObject->state = $state;
        $socialObject->code = $code;
        $socialObject->redirectUrl = Url::toRoute("{$this->config->route}/{$social}", true);

        if($register && $socialObject->register()) {
            $result = $socialObject->registerSuccess($this);
            \Yii::endProfile("Social profiling", static::class);
            return $result;
        }

        if(!$register && $socialObject->login($this->config->duration)) {
            $result =  $socialObject->loginSuccess($this);
            \Yii::endProfile("Social profiling", static::class);
            return $result;
        }

        \Yii::endProfile("Social profiling", static::class);
        return $socialObject->error($this);
    }
}