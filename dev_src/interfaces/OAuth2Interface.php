<?php

namespace Celebron\social\dev\interfaces;

use Celebron\social\dev\RequestCode;
use Celebron\social\dev\RequestId;
use Celebron\social\dev\RequestToken;
use Celebron\social\dev\SocialResponse;

interface OAuth2Interface extends SocialAuthInterface
{
    public function requestCode(RequestCode $request):void;
    public function requestToken(RequestToken $request): void;
    public function requestId(RequestId $request): SocialResponse;
}