<?php

namespace Celebron\social\old\interfaces;

use yii\httpclient\Request;

interface SetFullUrlInterface
{
    /**
     * @param Request $request
     */
    public function setFullUrl(Request $request):void;
}