<?php

namespace Celebron\social\interfaces;

use Celebron\social\RequestCode;
use Celebron\social\RequestId;
use Celebron\social\RequestToken;
use Celebron\social\SocialResponse;

interface OAuth2Interface extends SocialAuthInterface
{
    public function requestCode(RequestCode $request):void;
    public function requestToken(RequestToken $request): void;
    public function requestId(RequestId $request): SocialResponse;
}