<?php

namespace Celebron\social;

use Celebron\social\attrs\Request;
use yii\base\InvalidConfigException;
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
    private function handler(string $social, string $userClass, ?object $userObject)
    {
        \Yii::beginProfile("Social profiling", static::class);
        $object = $this->config->get($social);

        try {
            if($object === null) {
                throw  throw new NotFoundHttpException("Social '{$social}' not registered");
            }
            $userObject  =  $userObject ?? \Yii::createObject($userClass);
            $methodName = 'social' . $this->getState()->normalizeMethod();
            $methodRef = new \ReflectionMethod($userClass, $methodName);

            $attributes = $methodRef->getAttributes(Request::class);
            $requested = true;
            if(isset($attributes[0])) {
                /** @var Request $attr */
                $attr = $attributes[0]->newInstance();
                $requested = $attr->request;
            }

            if($requested) {
                \Yii::info("Request to {$social} server", static::class);
                $response = $object->request($this->code, $this->getState());
            } else {
                $response = new Response($object->socialName, null, null);
            }

            if($methodRef->invoke($userObject, $response, $object)) {
                \Yii::info("Invoke method ({$methodRef->getShortName()}) successful", static::class);
                return $object->success($this, $response);
            }

            \Yii::warning("Invoke method ({$methodRef->getShortName()}) failed", static::class);
            return $object->failed($this, $response);
        } catch (\Exception $ex) {
            \Yii::error($ex->getMessage(), static::class);
            return $object->error($this, $ex);
        } finally {
            \Yii::endProfile("Social profiling", static::class);
        }
    }

    /**
     * @throws BadRequestHttpException
     */
    public function actionHandler($social)
    {
        return $this->handler($social, \Yii::$app->user->identityClass, \Yii::$app->user->identity);
    }

}