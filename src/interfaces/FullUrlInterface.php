<?php

namespace Celebron\social\interfaces;
use yii\httpclient\Request;
interface FullUrlInterface
{
    /**
     * @param Request $request
     */
    public function setFullUrl (Request $request): string;
}