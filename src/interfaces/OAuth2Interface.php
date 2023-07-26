<?php

namespace Celebron\socialSource\interfaces;

use Celebron\socialSource\requests\CodeRequest;
use Celebron\socialSource\requests\IdRequest;
use Celebron\socialSource\requests\TokenRequest;
use Celebron\socialSource\ResponseSocial;

interface OAuth2Interface extends SocialInterface
{
    public function requestCode(CodeRequest $request):void;
    public function requestToken(TokenRequest $request):void;
    public function requestId(IdRequest $request):ResponseSocial;
//    public function requestRefreshToken(RefreshTokenRequest $request):void;
}