<?php

namespace Celebron\socialSource\requests;

use Celebron\socialSource\interfaces\OAuth2Interface;
use Celebron\socialSource\interfaces\UrlsInterface;
use Celebron\socialSource\OAuth2;
use Celebron\socialSource\State;

class CodeRequest
{
    public string $response_type = 'code';
    public string $client_id;
    public string $redirect_uri;
    public string $uri;
    public array $data = [];

    /**
     */
    public function __construct (protected OAuth2 $social, public State $state, array $config = [])
    {
        $this->uri = ($this->social instanceof  UrlsInterface) ? $this->social->getUriCode() : '';
        $this->client_id = $this->social->clientId;
        $this->redirect_uri = $this->social->redirectUrl;
    }

    public function generateUri() : array
    {
        $event = new EventData($this->data);
        $this->social->trigger(OAuth2::EVENT_DATA_CODE, $event);
        $this->data = $event->newData;

        if(empty($this->uri)) {
            throw new BadRequestHttpException('[RequestCode] Property $uri empty.');
        }

        $default = [
            0 => $this->uri,
            'response_type' => $this->response_type,
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'state' => (string)$this->state,
        ];
        return ArrayHelper::merge($default, $this->data);
    }
}