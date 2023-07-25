<?php

namespace Celebron\social\interfaces;

use Celebron\social\Configuration;
use Celebron\social\events\EventResult;
use Celebron\social\Response;
use Celebron\social\SocialController;
use Celebron\social\SocialResponse;
use yii\base\Component;

/**
 *
 * @property bool $active
 */
abstract class AbstractSocialAuth extends Component implements SocialAuthInterface
{
    public function __construct (
        protected readonly string $socialName,
        protected readonly Configuration $configure,
        $config = []
    )
    {
        parent::__construct($config);
    }

    /**
     * @param SocialController $controller
     * @param Response $response
     * @return mixed
     */
    public function success (SocialController $controller, Response $response): mixed
    {
        $event = new EventResult($controller, $response);
        $this->trigger(SocialAuthInterface::EVENT_SUCCESS, $event);
        return $event->result ?? $controller->goBack();
    }

    /**
     * @param SocialController $controller
     * @param Response $response
     * @return mixed
     */
    public function failed (SocialController $controller, Response $response): mixed
    {
        $event = new EventResult($controller, $response);
        $this->trigger(SocialAuthInterface::EVENT_FAILED, $event);
        return $event->result ?? $controller->goBack();
    }

    /**
     * @param array|\Closure|string|null $field
     * @param mixed $data
     * @return SocialResponse
     */
    public function response (array|\Closure|string|null $field, mixed $data): SocialResponse
    {
        return new SocialResponse($this->socialName, $field, $data);
    }


}