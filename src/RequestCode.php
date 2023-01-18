<?php

namespace Celebron\social;

use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\httpclient\Client;
use yii\web\BadRequestHttpException;
use yii\web\Cookie;

/**
 *
 * @property-read null|array $stateDecode
 */
class RequestCode extends BaseObject
{
    public string $response_type = 'code';
    public string $client_id;
    public string $redirect_uri;
    public ?string $state;
    public string $uri;

    public array $data = [];

    public bool $enable = true;


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
}