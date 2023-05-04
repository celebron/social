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


    public function getCode() : ?string
    {
       return $this->request->get('code');
    }

    public function getState() : State
    {
        $state = $this->request->get('state');
        if($state === null) {
            throw new BadRequestHttpException(
                \Yii::t('yii', 'Missing required parameters: {params}', ['params' => 'state'])
            );
        }

        return State::open($state);
    }

    /**
     * @param string $social
     * @return mixed|Response
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionHandler(string $social)
    {
        \Yii::beginProfile("Social profiling", static::class);
        $socialObj = $this->config->getSocial(strip_tags($social));
        try {
            if($socialObj === null) {
                throw  throw new NotFoundHttpException("Social '{$social}' not registered");
            }

            return $socialObj->run($this);

        } finally {
            \Yii::endProfile("Social profiling", static::class);
        }

    }
}