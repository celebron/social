<?php

namespace Celebron\socialSource;

use Celebron\socialSource\behaviors\ActiveBehavior;
use Celebron\socialSource\events\EventResult;
use Celebron\socialSource\interfaces\RequestInterface;
use Celebron\socialSource\interfaces\SocialInterface;
use yii\base\Component;
use yii\helpers\ArrayHelper;


/**
 * @property bool $active
 */
abstract class Request extends Component implements RequestInterface
{
    public const EVENT_SUCCESS = 'success';
    public const EVENT_FAILED = 'failed';
    public const EVENT_ERROR = 'error';

    public function __construct (
        public readonly string $socialName,
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

    public function response(string|\Closure|array|null $field, mixed $data) : ResponseSocial
    {
        return new ResponseSocial($this->socialName, $field, $data);
    }

}