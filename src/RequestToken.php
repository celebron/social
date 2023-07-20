<?php

namespace Celebron\social;

use Celebron\social\args\DataEventArgs;
use Celebron\social\interfaces\GetUrlsInterface;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;
use yii\httpclient\Request;
use yii\web\BadRequestHttpException;
use Yiisoft\Http\Header;

/**
 *
 * @property-write string $authorization
 * @property-write string $authorizationBasic
 */
class RequestToken extends BaseObject
{
    public array $data = [];
    public string $redirect_uri;
    public string $grant_type = 'authorization_code';
    public string $client_id;
    public string $client_secret;
    public string $uri;

    public bool $send = true;
    public array $header = [];
    public array $params = [];
    private readonly Client $client;
    public function __construct (
        public readonly string $code,
        protected OAuth2 $social,
        array $config = []
    ) {
        parent::__construct($config);
        $this->uri = ($this->social instanceof GetUrlsInterface) ? $this->social->getUriToken():'';
        $this->client_id = $this->social->clientId;
        $this->redirect_uri = $this->social->redirectUrl;
        $this->client_secret = $this->social->clientSecret;
        $this->client = $this->social->client;
    }

    public function setAuthorization(string $value) : void
    {
        $this->header[Header::AUTHORIZATION] = $value;
    }

    public function setAuthorizationBasic(string $value, bool $base64 = true) : void
    {
        $this->setAuthorization('Basic ' . ($base64 ? base64_encode($value):$value));
    }

    public function generateData(): array
    {
        $event = new DataEventArgs($this->data);
        $this->social->trigger(OAuth2::EVENT_DATA_TOKEN, $event);
        $this->data = $event->newData;

        return ArrayHelper::merge([
            'redirect_uri' => $this->redirect_uri,
            'grant_type' => $this->grant_type,
            'code' => $this->code,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
        ], $this->data);
    }

    public function sender() : Request
    {
        if(empty($this->uri)) {
            throw new BadRequestHttpException('[RequestToken] Property $uri empty.');
        }
        return $this->client->post($this->uri, $this->generateData(), $this->header, $this->params);
    }

}