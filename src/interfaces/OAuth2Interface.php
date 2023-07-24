<?php

namespace Celebron\social\interfaces;

use Celebron\social\RequestCode;
use Celebron\social\RequestId;
use Celebron\social\RequestToken;
use Celebron\social\SocialResponse;

interface OAuth2Interface extends SocialAuthInterface
{
    public function getClientId():string;
    public function setClientId(string $value):void;

    public function getClientSecret():string;
    public function setClientSecret(string $value):void;

    public function getRedirectUrl():string;
    public function setRedirectUrl(string $url):void;

    public function requestCode(RequestCode $request):void;
    public function requestToken(RequestToken $request): void;
    public function requestId(RequestId $request): SocialResponse;

    public function getUriCode (): string;
    public function getUriToken (): string;
    public function getUriInfo (): string;
    public function getUriRefreshToken ():string;
}