<?php

namespace Celebron\social;

use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
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
     * @param string $state
     * @param string|null $code
     * @return mixed|Response
     * @throws \Exception
     */
    public function actionHandler(string $social, string $state, ?string $code = null)
    {
        \Yii::beginProfile("Social profiling", static::class);

        $action = State::open($state);
        $socialObj = $this->config->getSocial($social);
        try {
            if($socialObj === null) {
                throw  throw new NotFoundHttpException("Social '{$social}' not registered");
            }

            $methodRef = new \ReflectionMethod($socialObj, $action->method);
            $attributes = $methodRef->getAttributes(Request::class);
            if (isset($attributes[0])) {
                $socialObj->request($code, $action);
            }

            if ($methodRef->invoke($socialObj, $this->config)) {
                return $socialObj->success($this);
            }

            return $socialObj->failed($this);
        } catch (\Exception $ex) {
            \Yii::error($ex->getMessage(), static::class);
            return $socialObj->error($this, $ex);
        } finally {
            \Yii::endProfile("Social profiling", static::class);
        }
    }
}