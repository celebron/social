<?php

namespace Celebron\socialSource;

use Celebron\socialSource\behaviors\ActiveBehavior;
use Celebron\socialSource\events\EventResult;
use Celebron\socialSource\interfaces\RequestInterface;
use Celebron\socialSource\interfaces\SocialInterface;
use Celebron\socialSource\interfaces\SocialUserInterface;
use yii\base\Component;
use yii\helpers\ArrayHelper;


/**
 * @property-read mixed $socialId
 * @property bool $active
 */
abstract class Social extends Component implements RequestInterface
{
    public const EVENT_SUCCESS = 'success';
    public const EVENT_FAILED = 'failed';
    public const EVENT_ERROR = 'error';

    public function __construct (
        public readonly string        $socialName,
        public readonly Configuration $configure,
                                      $config = [])
    {
        parent::__construct($config);
    }

    public function behaviors()
    {
        return [
          static::class => new ActiveBehavior($this->socialName, $this->configure),
        ];
    }

    public function success (HandlerController $controller, Response $response): mixed
    {
       $event = new EventResult($controller, $response);
       $this->trigger(self::EVENT_SUCCESS, $event);
       return $this->result ?? $controller->goBack();
    }

    public function failed (HandlerController $controller, Response $response): mixed
    {
        $event = new EventResult($controller, $response);
        $this->trigger(self::EVENT_FAILED, $event);
        return $this->result ?? $controller->goBack();
    }

    public function response (string|\Closure|array|null $field, mixed $data): ResponseSocial
    {
        return new ResponseSocial($this->socialName, $field, $data);
    }

    public function url (string $action, string $state = null): string
    {
        return $this->configure->url($this->socialName, $action, $state);
    }

    public function getSocialId (): mixed
    {
        /** @var SocialUserInterface $user */
        $user = \Yii::$app->user->identity;
        $field = $user->getSocialField($this->socialName);
        return $user->$field;
    }

}