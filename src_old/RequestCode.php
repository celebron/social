<?php

namespace Celebron\src_old;

use Celebron\social\old\interfaces\GetUrlsInterface;
use yii\base\BaseObject;
use yii\base\Exception;
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
     * @throws Exception
     */
    public function __construct (OAuth2 $social, public State $state, array $config = [])
    {
        parent::__construct($config);
        $this->uri = ($social instanceof  GetUrlsInterface) ? $social->getUriCode() : '';
        $this->client_id = $social->clientId;
        $this->redirect_uri = $social->redirectUrl;
    }

    public function generateUri() : array
    {
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