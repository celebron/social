<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\socialSource;

use Celebron\common\TokenInterface;
use Celebron\socialSource\events\EventError;
use Celebron\socialSource\interfaces\SocialInterface;
use Celebron\socialSource\interfaces\SocialUserInterface;
use Celebron\socialSource\responses\Id;
use yii\base\NotSupportedException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\IdentityInterface;
use yii\web\NotFoundHttpException;

/**
 *
 * @property-read null|string $code
 * @property-read \Celebron\socialSource\State $state
 */
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
                throw new NotFoundHttpException(\Yii::t('social',"Social '{socialName}' not found",[
                    'socialName' => $social,
                ]));
            }
            if (!$object->active) {
                throw new BadRequestHttpException(\Yii::t('social',"Social '{socialName}' not active",[
                    'socialName' => $social,
                ]));
            }

            /** @var  Model&IdentityInterface $userObject */
            $userObject = \Yii::$app->user->identity ?? \Yii::createObject(\Yii::$app->user->identityClass);
            $methodName = 'social' . $this->getState()->normalizeMethod();
            $refMethod = new \ReflectionMethod($userObject, $methodName);

            if (!$refMethod->getDeclaringClass()->implementsInterface(SocialUserInterface::class)) {
                throw new NotSupportedException('Class "' . \Yii::$app->user->identityClass . '" not implement ' . SocialUserInterface::class);
            }

            $refAttrs = $refMethod->getAttributes(Secure::class, ReflectionAttribute::IS_INSTANCEOF);
            $secure = true;
            if(null !== ($refAttr = $refAttrs[0] ?? null)) {
                /** @var Secure $attr */
                $attr = $refAttr->newInstance();
                $secure = $attr->secure($object, $userObject);
            }

            $args = [];
            foreach ($refMethod->getParameters() as $key => $parameter) {
                $type = (string)$parameter->getType();
                $typeClassRef = new \ReflectionClass($type);
                if ($type === self::class) {
                    $args[$key] = $this;
                } elseif ($type === Id::class) {
                    $args[$key] = $object->request($this->getCode(), $this->getState(), $this->request->getBodyParams());
                } elseif ($typeClassRef->implementsInterface(SocialInterface::class)) {
                    $args[$key] = $object;
                } elseif ($typeClassRef->implementsInterface(TokenInterface::class)) {
                    $args[$key] = $object->handleToken($this->getCode());
                }
            }

            //Выполняем метод, если $secure = true;
            $response = $secure ? $refMethod->invokeArgs($userObject, $args) : false;
            if (is_bool($response)) {
                $response = new Response($response, "Use method {$refMethod->getName()}");
            }

            if($response->success) {
                \Yii::info("User from social '$social' authorized success", static::class);
                return $object->success($this, $response);
            }
            \Yii::warning("User from social '$social' authorized failed", static::class);
            return $object->failed($this, $response);
        } catch (\Exception $ex) {
            \Yii::error((string)$ex, static::class);
            $event = new EventError($this, $ex);
            $object?->trigger(Social::EVENT_ERROR, $event);
            if (empty($event->result)) {
                throw $event->exception;
            }
            return $event->result;
        } finally {
            \Yii::endProfile("Social profiling", static::class);
        }
    }

}