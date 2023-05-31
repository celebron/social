<?php

namespace Celebron\social\interfaces;

use Celebron\social\OAuth2;
use Celebron\social\Response;

interface SocialRequestInterface
{
    public function socialLogin(Response $response, OAuth2 $auth):bool;
    public function socialRegister(Response $response, OAuth2 $auth):bool;
}