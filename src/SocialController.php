<?php

namespace Celebron\social;

use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

/**
 * Контролер
 */
class SocialController extends \yii\web\Controller
{
    /** @var SocialConfiguration - Конфигурация */
    public SocialConfiguration $config;


    /**
     * @throws UnauthorizedHttpException
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     */
    public function actionDelete(string $social)
    {
        \Yii::beginProfile("Social profiling | delete", static::class);
        if(\Yii::$app->user->isGuest) {
            throw new UnauthorizedHttpException();
        }
        try {
            $socialObject = $this->config->getSocial($social);

            if ($socialObject->delete()) {
                return $socialObject->deleteSuccess($this);
            }

            return $socialObject->error($this, new HttpException(400,"[$social]Not delete from userid " . \Yii::$app->user->id));
        } catch (\Exception $ex) {
            return $socialObject->error($this, $ex);
        } finally {
            \Yii::endProfile('Social profiling | delete', static::class);
        }
    }

    /**
     * @param string $social
     * @param string|null $code
     * @param string|null $state
     * @return mixed|Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionHandler(string $social, ?string $code = null, ?string $state = null)
    {
        $register = ($state !== null) && str_contains($this->config->register, $state);

        \Yii::beginProfile("Social profiling", static::class);
        try {
            $socialObject = $this->config->getSocial($social, Social::SCENARIO_LOGONED);
            $socialObject->state = $state;
            $socialObject->code = $code;
            $socialObject->redirectUrl = Url::toRoute("{$this->config->route}/{$social}", true);

            if ($register && $socialObject->register()) {
                return $socialObject->registerSuccess($this);
            }

            if (!$register && $socialObject->login($this->config->duration)) {
                return $socialObject->loginSuccess($this);
            }

            return $socialObject->error($this, new NotFoundHttpException("[$social]User ' . {$socialObject->id} .' not registered by site"));
        } catch (\Exception $ex) {
            return $socialObject->error($this, $ex);
        } finally {
            \Yii::endProfile("Social profiling", static::class);
        }
    }
}