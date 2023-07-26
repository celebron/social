<?php

namespace Celebron\socialSource;

use Celebron\socialSource\events\EventError;
use Celebron\socialSource\interfaces\SocialInterface;
use Celebron\socialSource\interfaces\SocialUserInterface;
use yii\base\NotSupportedException;
use yii\base\UnknownClassException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

class HandlerController extends Controller
{
    public Configuration $configure;


    public function getCode():?string
    {
        return $this->request->get('code');
    }

    public function getState():State
    {
        $state = $this->request->get('state');
        if(isset($state)) {
            return State::open($state);
        }
        throw  new BadRequestHttpException(\Yii::t('yii', 'Missing required parameters: {params}', ['params' => 'state']));
    }

    public function actionHandler(string $social)
    {
        \Yii::beginProfile("Social profiling", static::class);
        $object = $this->configure->getSocial($social);
        try {
            if (is_null($object)) {
                throw new NotFoundHttpException("Social '$social' not found");
            }
            if (!$object->active) {
                throw new BadRequestHttpException("Social '$social' not active");
            }

            $userObject = \Yii::$app->user->identity ?? \Yii::createObject(\Yii::$app->user->identityClass);
            $methodName = 'social' . $this->getState()->normalizeMethod();
            $methodRef = new \ReflectionMethod($userObject, $methodName);

            if (!$methodRef->getDeclaringClass()->implementsInterface(SocialUserInterface::class)) {
                throw new NotSupportedException('Class "' . \Yii::$app->user->identityClass . '" not implement ' . SocialUserInterface::class);
            }

            $args = [];
            foreach ($methodRef->getParameters() as $key => $parameter) {
                $type = (string)$parameter->getType();
                $typeClassRef = new \ReflectionClass($type);
                if ($type === self::class) {
                    $args[$key] = $this;
                }
                if ($type === ResponseSocial::class) {
                    $args[$key] = $object->request($this->getCode(), $this->getState());
                }
                if ($typeClassRef->implementsInterface(SocialInterface::class)) {
                    $args[$key] = $object;
                }
            }


            $response = $methodRef->invokeArgs($userObject, $args);
            if (is_bool($response)) {
                $response = new Response($response);
            }

//            if($response->success) {
//                \Yii::info("User from social '$social' authorized success", static::class);
//                return $object->success($this, $response);
//            }
//            \Yii::warning("User from social '$social' authorized failed", static::class);
//            return $object->failed($this, $response);
        } catch (\Exception $ex) {
            \Yii::error("Erorr");
            $event = new EventError($this, $ex);
            $object?->trigger(Request::EVENT_ERROR, $event);
            if (empty($event->result)) {
                throw $event->exception;
            }
            //return $event->result;
            return $this->renderContent($ex->getMessage());
        } finally {
            \Yii::endProfile("Social profiling", static::class);
        }
    }

}