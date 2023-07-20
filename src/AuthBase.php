<?php

namespace Celebron\social;

use Celebron\social\interfaces\SocialInterface;
use Celebron\social\args\{ErrorEventArgs, ResultEventArgs};
use yii\base\Component;
use yii\base\Event;
use yii\base\NotSupportedException;


/**
 *
 * @property-read mixed $socialId
 */
abstract class AuthBase extends Component
{
    public const EVENT_SUCCESS = 'success';
    public const EVENT_FAILED = 'failed';
    public const EVENT_ERROR = 'error';
    public bool $active = false;

    abstract public function request(?string $code, State $state):Response;

    public function __construct (
        public readonly string        $socialName,
        public readonly Configuration $config,
        array                         $cfg = []
    ){
        parent::__construct($cfg);
    }

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

    public function response(string|\Closure|array|null $field, mixed $data) : Response
    {
        return new Response($this->socialName, $field,$data);
    }

    public function getSocialId():mixed
    {
        $user = \Yii::$app->user->identity;
        if($user instanceof SocialInterface) {
            //return $user->getSocialId($this->socialName);
            $field = $user->getSocialField($this->socialName);
            return $user->$field;
        }
        throw new NotSupportedException('Not released ' . SocialInterface::class);
    }

    public static function ToException(?self $base, SocialController $controller, \Exception $ex)
    {
        $error = new ErrorEventArgs($controller, $ex);
        if(isset($base)) {
            $base->trigger(self::EVENT_ERROR, $error);
        } else {
            Event::trigger(static::class, self::EVENT_ERROR, $error);
        }

        if($error->result === null) {
            throw $error->exception;
        }
        return $error->result;
    }
}