<?php

namespace Celebron\social\interfaces;

use yii\httpclient\Request;

interface SetFullUrlInterface
{
    /**
     * @param Request $request
     * @return mixed
     */
    public function setFullUrl(Request $request);
}