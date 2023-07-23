<?php

namespace Celebron\social;

use Celebron\social\interfaces\SocialAuthInterface;
use Celebron\social\interfaces\SocialInterface;
use Celebron\social\args\{EventError, EventResult};
use yii\base\Component;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;


/**
 *
 * @property bool $active
 * @property-read mixed $socialId
 */
abstract class SocialAuthBase extends Component implements SocialAuthInterface
{
    public const EVENT_SUCCESS = 'success';
    public const EVENT_FAILED = 'failed';
    public const EVENT_ERROR = 'error';
    public bool $_active = false;

    public function __construct (
        public readonly string        $socialName,
        public readonly Configuration $config,
        array                         $cfg = []
    ){
        parent::__construct($cfg);
    }

    public function success(SocialController $action, Response $response): mixed
    {
        $eventArgs = new EventResult($action, $response);
        $this->trigger(self::EVENT_SUCCESS, $eventArgs);
        return $eventArgs->result ?? $action->goBack();
    }

    public function failed(SocialController $action, Response $response): mixed
    {
        $eventArgs = new EventResult($action, $response);
        $this->trigger(self::EVENT_FAILED, $eventArgs);
        return $eventArgs->result ?? $action->goBack();
    }

    public function response(string|\Closure|array|null $field, mixed $data) : SocialResponse
    {
        return new SocialResponse($this->socialName, $field, $data);
    }

    public function getSocialId():mixed
    {
        $user = \Yii::$app->user->identity;
        if($user instanceof SocialInterface) {
            $field = $user->getSocialField($this->socialName);
            return $user->$field;
        }
        throw new NotSupportedException('Not released ' . SocialInterface::class);
    }

    public static function ToException(?self $base, SocialController $controller, \Exception $ex)
    {
        $error = new EventError($controller, $ex);
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

    public function getActive (): bool
    {
        if (isset($this->config->paramsGroup, \Yii::$app->params[$this->config->paramsGroup][$this->socialName]['active'])) {
            return \Yii::$app->params[$this->config->paramsGroup][$this->socialName]['active'];
        }
        return $this->_active;
    }

    public function setActive (bool $value): void
    {
        $this->_active = $value;
    }
}