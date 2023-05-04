<?php

namespace Celebron\social;

use Celebron\social\eventArgs\ErrorEventArgs;
use Celebron\social\eventArgs\ResultEventArgs;
use Celebron\social\interfaces\RequestInterface;
use yii\base\Model;
use yii\web\NotFoundHttpException;
use yii\web\Response;

abstract class AuthBase extends Model
{
    public const EVENT_ERROR = "error";
    public const EVENT_SUCCESS = 'success';
    public const EVENT_FAILED = 'failed';

    public bool $active = true;

    /**
     * @throws \Exception
     */
    public function run(SocialController $controller): mixed
    {
        $action = $controller->getState();
        try {
            $methodRef = new \ReflectionMethod($this, $action->method);

            if($this instanceof RequestInterface)
            {
                $this->request($methodRef, $controller);
            }

            if ($methodRef->invoke($this, $controller->config)) {
                return $this->success($methodRef->getShortName(), $controller);
            }

            return $this->failed($methodRef->getShortName(), $controller);
        } catch (\Exception $ex) {
            \Yii::error($ex->getMessage(), static::class);
            return $this->error($action->method, $controller, $ex);
        }
    }

    protected function success(string $method, SocialController $action): mixed
    {
        $eventArgs = new ResultEventArgs($action, $method);
        $this->trigger(self::EVENT_SUCCESS, $eventArgs);
        return $eventArgs->result ?? $action->goBack();
    }

    protected function failed(string $method, SocialController $action): mixed
    {
        $eventArgs = new ResultEventArgs($action, $method);
        $this->trigger(self::EVENT_FAILED, $eventArgs);
        return $eventArgs->result ?? $action->goBack();
    }

    /**
     * @throws \Exception
     */
    protected function error(string $method, SocialController $action, \Exception $ex): mixed
    {
        $eventArgs = new ErrorEventArgs($action, $method, $ex);
        $this->trigger(self::EVENT_ERROR, $eventArgs);
        if($eventArgs->result === null) {
            throw $eventArgs->exception;
        }
        return $eventArgs->result;
    }

    final public static function socialName(): string
    {
        $reflect = new \ReflectionClass(static::class);
        $attributes = $reflect->getAttributes(SocialName::class);
        $socialName = $reflect->getShortName();
        if(count($attributes) > 0) {
            $socialName = $attributes[0]->getArguments()[0];
        }
        return $socialName;
    }
}