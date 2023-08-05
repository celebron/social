<?php

namespace Celebron\socialSource;

use Celebron\socialSource\behaviors\ActiveBehavior;
use Celebron\socialSource\events\EventResult;
use Celebron\socialSource\interfaces\RequestInterface;
use Celebron\socialSource\interfaces\SocialInterface;
use Celebron\socialSource\interfaces\SocialUserInterface;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;


/**
 * @property-read mixed $socialId
 * @property bool $active
 */
abstract class Social extends Component implements RequestInterface
{
    public const EVENT_SUCCESS = 'success';
    public const EVENT_FAILED = 'failed';
    public const EVENT_ERROR = 'error';

    protected readonly array $params;

    public function __construct (
        public readonly string        $socialName,
        public readonly Configuration $configure,
                                      $config = [])
    {
        $this->params = ArrayHelper::getValue(\Yii::$app->params, [$this->configure->paramsGroup, $this->socialName], []);
        parent::__construct($config);
    }

    private bool $_active = false;

    /**
     * @return bool
     */
    public function getActive (): bool
    {
        return $this->params['active'] ?? $this->_active;
    }
    public function setActive (bool $value): void
    {
        $this->_active = $value;
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

    public static function urlStatic(string $socialName, string $action,?string $state = null, string $socialComponent = 'social'):string
    {
        return (new static($socialName,  \Yii::$app->get($socialComponent)))->url($action, $state);
    }

    public function __call ($methodName, $params)
    {
        $prefix = 'url';
        if(StringHelper::startsWith($methodName, $prefix)) {
            $actionName = strtolower(substr($methodName, strlen($prefix)));
            return $this->url($actionName, $params[0]??null);
        }
        return parent::__call($methodName, $params);
    }

    public static function __callStatic ($name, $arguments)
    {
        // TODO: Implement __callStatic() method.
    }
}