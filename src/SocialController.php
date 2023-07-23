<?php

namespace Celebron\social;

use Celebron\social\args\EventResult;
use Celebron\social\attrs\SocialRequest;
use yii\base\InvalidArgumentException;
use yii\base\InvalidParamException;
use yii\filters\auth\AuthInterface;
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
    public Configuration $config;

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
    public function actionHandler($social)
    {
        \Yii::beginProfile("Social profiling", static::class);
        $object = $this->config->get($social);

        try {
            //Если не поддерживается
            if ($object === null) {
                throw new NotFoundHttpException("Social '$social' not registered");
            }

            //Если не активна
            if (!$object->active) {
                throw new NotFoundHttpException("Social '$social' not active");
            }

            $methodName = 'social' . $this->getState()->normalizeMethod();
            $methodRef = new \ReflectionMethod(\Yii::$app->user->identityClass, $methodName);

            //Обработка параметров
            $args = [];
            foreach ($methodRef->getParameters() as $key => $parameter) {
                if ($parameter->hasType()) {
                    $type = (string)$parameter->getType();
                    $typeClassRef = new \ReflectionClass($type);
                    if ($typeClassRef->implementsInterface(AuthInterface::class)) {
                        $args[$key] = $object;
                    }
                    if ($type === self::class) {
                        $args[$key] = $this;
                    }
                    if ($type === SocialResponse::class) {
                        $args[$key] = $object->request($this->getCode(), $this->getState());
                    }
                } else {
                    throw new InvalidArgumentException();
                }
            }

            if (!$methodRef->hasReturnType() ||
                !((string)$methodRef->getReturnType() === 'bool' || (string)$methodRef->getReturnType() === Response::class)
            ) {
                throw new \http\Exception\InvalidArgumentException();
            }

            $response = $methodRef->invokeArgs(\Yii::$app->user->identity, $args);
            if(is_bool($response)) {
                $response = new Response($response);
            }

            if($response->success) {
                \Yii::info("Invoke method ({$methodRef->getShortName()}) successful", static::class);
                return $object->success($this, $response);
            }

            \Yii::warning("Invoke method ({$methodRef->getShortName()}) failed", static::class);
            return $object->failed($this, $response);
        }
        catch (\Exception $ex) {
            \Yii::error($ex->getMessage(), static::class);
            return AuthBase::ToException($object, $this, $ex);
        } finally {
            \Yii::endProfile("Social profiling", static::class);
        }
    }

}