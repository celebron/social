<?php

namespace Celebron\social;

use Celebron\social\args\RequestArgs;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 *
 * @property-read null|string $code
 * @property-read State $state
 */
class SocialController extends Controller
{
    public SocialConfiguration $config;

    public function getCode() : ?string
    {
        return $this->request->get('code');
    }

    /**
     * @throws BadRequestHttpException
     */
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
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function actionHandler(string $social)
    {
        \Yii::beginProfile("Social profiling", static::class);
        $object = $this->config->get($social);

        try {
            if($object === null) {
                throw  throw new NotFoundHttpException("Social '{$social}' not registered");
            }

            $userClass = \Yii::$app->user->identityClass;
            $response = $object->request($this->code, $this->getState(), $this->config);
            $objectUser = \Yii::createObject($userClass);

            $methodName = $this->config->prefixMethod . $this->getState()->normalizeMethod();
            $methodRef = new \ReflectionMethod($userClass, $methodName);
            if($methodRef->invoke($objectUser, $response, $object)) {
                return $object->success($this, $response);
            }
            return $object->failed($this, $response);
        } catch (\Exception $ex) {
            \Yii::error($ex->getMessage(), static::class);
            return $object->error($this, $ex);
        } finally {
            \Yii::endProfile("Social profiling", static::class);
        }
    }
}