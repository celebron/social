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

    /**
     * @throws BadRequestHttpException
     */
    public function getStateDecode() : ?array
    {
        if($this->state !== null ) {
            return Json::decode(base64_decode($this->state));
        }
        throw new BadRequestHttpException('Empty $state');
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

    /**
     * @throws BadRequestHttpException
     */
    public function toClient(Client $client) : Never
    {
        $url = $client->get($this->generateUri());
        $session = \Yii::$app->session;
        $data = $this->getStateDecode();
        if(!$session->isActive) { $session->open(); }
        $session['social_random'] = $data['random'];
        \Yii::$app->response->redirect($url->getFullUrl(), checkAjax: false)->send();
        exit(0);
    }



}