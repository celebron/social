<?php

namespace Celebron\social;

use Celebron\social\args\RequestCodeEventArgs;
use Celebron\social\args\RequestEventArgs;
use Celebron\social\interfaces\GetUrlsInterface;
use yii\base\BaseObject;
use yii\base\Event;
use yii\helpers\ArrayHelper;


/**
 *
 * @property null|array $state
 */
class RequestCode extends BaseObject
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
        parent::__construct($config);
        $this->uri = ($this->social instanceof  GetUrlsInterface) ? $social->getUriCode() : '';
        $this->client_id = $this->social->clientId;
        $this->redirect_uri = $this->social->redirectUrl;
    }

    public function generateUri() : array
    {
        $event = new RequestCodeEventArgs($this->data);
        $this->social->trigger(OAuth2::EVENT_GENERATE, $event);
        $this->data = $event->newData;

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