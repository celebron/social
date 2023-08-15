<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\socialSource\interfaces;

use Celebron\common\Token;
use Celebron\socialSource\data\CodeData;
use Celebron\socialSource\data\IdData;
use Celebron\socialSource\data\TokenData;
use Celebron\socialSource\responses\Code;
use Celebron\socialSource\responses\Id;

interface OAuth2Interface extends SocialInterface
{
    public function requestCode(CodeData $request):Code;
    public function requestToken(TokenData $request):Token;
    public function requestId(IdData $request):Id;
//    public function requestRefreshToken(RefreshTokenRequest $request):void;
}