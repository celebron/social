<?php

namespace Celebron\social\interfaces;

use Celebron\social\Response;
use Celebron\social\SocialController;

interface SocialAuthInterface
{
    public const EVENT_SUCCESS = 'success';
    public const EVENT_FAILED = 'failed';
    public const EVENT_ERROR = 'error';

    public function success(SocialController $controller, Response $response):mixed;
    public function failed(SocialController $controller, Response $response):mixed;
}