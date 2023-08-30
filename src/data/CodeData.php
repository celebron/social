<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social\data;

use Celebron\source\social\interfaces\UrlFullInterface;
use Celebron\source\social\interfaces\UrlsInterface;
use Celebron\source\social\OAuth2;
use Celebron\source\social\responses\Code;
use Celebron\source\social\State;
use yii\base\Event;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

class CodeData extends AbstractData
{
    public string $response_type = 'code';

    /**
     */
    public function __construct (OAuth2 $social, public State $state, array $config = [])
    {
        parent::__construct($social, $config);
        $this->setUri(($this->social instanceof  UrlsInterface) ? $this->social->getUriCode() : '');
    }

    /**
     * @throws BadRequestHttpException
     */
    public function generateData(array $data) : array
    {
        $event = new Event(['data'=>$data]);
        $this->social->trigger(OAuth2::EVENT_DATA_CODE, $event);

        $default = [
            0 => $this->getUri(),
            'response_type' => $this->response_type,
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'state' => (string)$this->state,
        ];
        return ArrayHelper::merge($default, (array)$event->data);
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