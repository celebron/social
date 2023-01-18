<?php

namespace Celebron\social;

use yii\base\InvalidConfigException;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Контролер
 */
class SocialController extends Controller
{
    /** @var SocialConfiguration - Конфигурация */
    public SocialConfiguration $config;


    /**
     * @param string $social
     * @param string|null $code
     * @param string|null $state
     * @return mixed|Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
     */
    public function actionHandler(string $social, ?string $code = null, string $state = null): mixed
    {
        \Yii::beginProfile("Social profiling", static::class);

        $socialObject = $this->config->getSocial($social);
        $socialObject->state = $state;
        $socialObject->code = $code;
        $socialObject->redirectUrl = Url::toRoute("{$this->config->route}/{$social}", true);
        try {
            return $socialObject->run($this);
        } catch (HttpException $ex) {
            \Yii::error($ex->getMessage(), static::class);
            return $socialObject->error($this, $ex);
        } finally {
            \Yii::endProfile("Social profiling", static::class);
        }
    }
}