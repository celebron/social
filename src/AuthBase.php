<?php

namespace Celebron\social;

use Celebron\social\args\{ErrorEventArgs, RequestArgs, ResultEventArgs};
use yii\base\Model;


abstract class AuthBase extends Model
{
    public const EVENT_SUCCESS = 'success';
    public const EVENT_FAILED = 'failed';
    public const EVENT_ERROR = 'error';
    public bool $active = false;

    abstract public function request(RequestArgs $args):Response;

    public function success(SocialController $action, RequestArgs $args): mixed
    {
        $eventArgs = new ResultEventArgs($action, $args);
        $this->trigger(self::EVENT_SUCCESS, $eventArgs);
        return $eventArgs->result ?? $action->goBack();
    }

    public function failed(SocialController $action, RequestArgs $args): mixed
    {
        $eventArgs = new ResultEventArgs($action, $args);
        $this->trigger(self::EVENT_FAILED, $eventArgs);
        return $eventArgs->result ?? $action->goBack();
    }

    /**
     * @throws \Exception
     */
    public function error(SocialController $action, \Exception $ex, RequestArgs $args): mixed
    {
        $eventArgs = new ErrorEventArgs($action, $args, $ex);
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
        return SocialConfig::url(static::socialName(), $method, $state);
    }
}