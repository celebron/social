<?php

namespace Celebron\social;

use Celebron\social\args\EventResult;
use Celebron\social\attrs\SocialRequest;
use Celebron\social\interfaces\SocialAuthInterface;
use yii\base\InvalidArgumentException;
use yii\base\InvalidParamException;
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
            $userObject  =  \Yii::$app->user->identity ?? \Yii::createObject(\Yii::$app->user->identityClass);

            //Обработка параметров
            $args = [];
            foreach ($methodRef->getParameters() as $key => $parameter) {
                if ($parameter->hasType()) {
                    $type = (string)$parameter->getType();
                    $typeClassRef = new \ReflectionClass($type);
                    //Добавления объекта авторизации
                    if ($typeClassRef->implementsInterface(SocialAuthInterface::class)) {
                        $args[$key] = $object;
                    }
                    //Добавление контролера
                    if ($type === self::class) {
                        $args[$key] = $this;
                    }
                    //Добавление SocialResponse и выполнение request
                    if ($type === SocialResponse::class) {
                        $args[$key] = $object->request($this->getCode(), $this->getState());
                    }
                } else {
                    throw new InvalidArgumentException('The type is not defined');
                }
            }

            //Проверка выводимого значения метода обработки
            if (!$methodRef->hasReturnType() ||
                !((string)$methodRef->getReturnType() === 'bool' || (string)$methodRef->getReturnType() === Response::class)
            ) {
                throw new \http\Exception\InvalidArgumentException('ReturnType is not defined correctly');
            }

            $response = $methodRef->invokeArgs($userObject, $args);
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
            return SocialAuthBase::ToException($object, $this, $ex);
        } finally {
            \Yii::endProfile("Social profiling", static::class);
        }
    }

}