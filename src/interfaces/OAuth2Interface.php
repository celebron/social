<?php

namespace Celebron\socialSource\interfaces;

use Celebron\common\Token;
use Celebron\socialSource\data\CodeData;
use Celebron\socialSource\data\IdData;
use Celebron\socialSource\data\TokenData;
use Celebron\socialSource\responses\CodeRequest;
use Celebron\socialSource\responses\IdResponse;

interface OAuth2Interface extends SocialInterface
{
    public function requestCode(CodeData $request):CodeRequest;
    public function requestToken(TokenData $request):Token;
    public function requestId(IdData $request):IdResponse;
//    public function requestRefreshToken(RefreshTokenRequest $request):void;
}