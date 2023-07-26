<?php

namespace Celebron\socialSource\interfaces;

use yii\httpclient\Request;

interface UrlFullInterface
{
    public function fullUrl(Request $request):string;
}