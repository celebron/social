<?php

namespace Celebron\social;

use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\httpclient\Client;
use yii\web\Cookie;

class RequestCode extends BaseObject
{
    public string $response_type = 'code';
    public string $client_id;
    public string $redirect_uri;
    public string $state;
    public string $uri;

    public array $data = [];

    public bool $enable = true;

    public function getStateDecode() : array
    {
        return Json::decode(base64_decode($this->state));
    }

    public function generateUri() : array
    {
        $default = [
            0 => $this->uri,
            'response_type' => $this->response_type,
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'state' => $this->state
        ];
        return ArrayHelper::merge($default, $this->data);
    }

    public function toClient(Client $client) : Never
    {
        $url = $client->get($this->generateUri());
        //Записать cookie и отправить на сервер данные
        $cookie = \Yii::$app->response->cookies;
        $data = $this->getStateDecode();
        $cookie->add(new Cookie([
            'name'=>'social.random',
            'value'=> $data['random']
        ]));
        \Yii::$app->response->redirect($url->getFullUrl(), checkAjax: false)->send();
        exit(0);
    }



}