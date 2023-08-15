<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\socialSource\data;

use Celebron\socialSource\events\EventData;
use Celebron\socialSource\interfaces\UrlFullInterface;
use Celebron\socialSource\interfaces\UrlsInterface;
use Celebron\socialSource\OAuth2;
use Celebron\socialSource\responses\Code;
use Celebron\socialSource\State;
use yii\helpers\ArrayHelper;
use yii\httpclient\Request as ClientRequest;
use yii\httpclient\Response as ClientResponse;
use yii\web\BadRequestHttpException;

class CodeData extends AbstractData
{
    public string $response_type = 'code';

    /**
     */
    public function __construct (OAuth2 $social, public State $state, array $config = [])
    {
        parent::__construct($social, $config);
        $this->uri = ($this->social instanceof  UrlsInterface) ? $this->social->getUriCode() : '';
    }

    public function generateData(array $data) : array
    {
        $event = new EventData($data);
        $this->social->trigger(OAuth2::EVENT_DATA_CODE, $event);
        $data = $event->newData;

        $default = [
            0 => $this->uri,
            'response_type' => $this->response_type,
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'state' => (string)$this->state,
        ];
        return ArrayHelper::merge($default, $data);
    }

    public function request(array $data = [], array $headers = []):Code
    {
        if(empty($this->uri)) {
            throw new BadRequestHttpException(\Yii::t('social','[{request}]Property $uri empty.',[
                'request' => 'requestCode'
            ]));
        }
        $request = $this->client->get($this->generateData($data), headers: $headers);
        if ($this instanceof UrlFullInterface) {
            $request->setFullUrl($this->fullUrl($request));
        }
        return new Code($request, $this);
    }

}