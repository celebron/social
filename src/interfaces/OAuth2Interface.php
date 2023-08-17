<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social\interfaces;

use Celebron\common\Token;
use Celebron\source\social\data\CodeData;
use Celebron\source\social\data\IdData;
use Celebron\source\social\data\TokenData;
use Celebron\source\social\responses\Code;
use Celebron\source\social\responses\Id;

interface OAuth2Interface extends SocialInterface
{
    public function requestCode(CodeData $request):Code;
    public function requestToken(TokenData $request):Token;
    public function requestId(IdData $request):Id;
//    public function requestRefreshToken(RefreshTokenRequest $request):void;
}