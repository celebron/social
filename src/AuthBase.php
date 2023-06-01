<?php

namespace Celebron\social;

use Celebron\social\args\{ErrorEventArgs, ResultEventArgs};
use Celebron\social\attrs\SocialName;
use Celebron\social\interfaces\SocialInterface;
use yii\base\Model;
use yii\base\NotSupportedException;


abstract class AuthBase extends Model
{
    public const EVENT_SUCCESS = 'success';
    public const EVENT_FAILED = 'failed';
    public const EVENT_ERROR = 'error';
    public bool $active = false;

    abstract public function request(?string $code, State $state, SocialConfiguration $config):Response;

    public function success(SocialController $action, Response $response): mixed
    {
        $eventArgs = new ResultEventArgs($action, $response);
        $this->trigger(self::EVENT_SUCCESS, $eventArgs);
        return $eventArgs->result ?? $action->goBack();
    }

    public function failed(SocialController $action, Response $response): mixed
    {
        $eventArgs = new ResultEventArgs($action, $response);
        $this->trigger(self::EVENT_FAILED, $eventArgs);
        return $eventArgs->result ?? $action->goBack();
    }

    /**
     * @throws \Exception
     */
    public function error(SocialController $action, \Exception $ex): mixed
    {
        $eventArgs = new ErrorEventArgs($action, $ex);
        $this->trigger(self::EVENT_ERROR, $eventArgs);
        if($eventArgs->result === null) {
            throw $eventArgs->exception;
        }
        return $eventArgs->result;
    }

    protected function response(string|\Closure|array $field, mixed $data): Response
    {
        return new Response(static::socialName(), $field, $data);
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

    /**
     * Ссылка на oauth2 авторизацию
     * @param string $method
     * @param string|null $state
     * @return string
     */
    final public static function url(string $method, ?string $state = null) : string
    {
        return SocialConfiguration::url(static::socialName(), $method, $state);
    }

    public static function getSocialId(): mixed
    {
        $user = \Yii::$app->user->identity;
        if($user instanceof SocialInterface) {
            return $user->getSocialId(static::socialName());
        }
        throw new NotSupportedException('Not released ' . SocialInterface::class);
    }

}