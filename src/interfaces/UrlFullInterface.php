<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\socialSource\interfaces;

use yii\httpclient\Request;

interface UrlFullInterface
{
    public function fullUrl(Request $request):string;
}