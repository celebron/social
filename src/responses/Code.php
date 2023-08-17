<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social\responses;

use Celebron\source\social\data\CodeData;
use yii\base\BaseObject;
use yii\base\InvalidRouteException;
use yii\console\ExitCode;
use yii\httpclient\Client;
use yii\httpclient\Exception;
use yii\httpclient\Request;
use yii\httpclient\Response as ClientResponse;
use yii\web\BadRequestHttpException;

/**
 *
 * @property-read string $url
 */
class Code extends BaseObject
{
    public function __construct (
        protected Request  $request,
        protected CodeData $data,
        array              $config = [])
    {
        parent::__construct($config);
    }

    /**
     * @throws InvalidRouteException
     */
    public function redirect():never
    {
        \Yii::$app->response->redirect($this->getUrl(), checkAjax: false)->send();
        exit(ExitCode::OK);
    }

    public function getUrl():string
    {
        return $this->request->getFullUrl();
    }

    public function __toString ()
    {
        return $this->getUrl();
    }

    public function send()
    {
        return $this->data->send($this->request);
    }
}