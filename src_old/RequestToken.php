<?php

namespace Celebron\src_old;

use Celebron\social\old\interfaces\GetUrlsInterface;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;
use yii\httpclient\Request;
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

    public readonly string  $code;
    private readonly Client $client;
    public function __construct (string $code, OAuth2 $social, array $config = [])
    {
        parent::__construct($config);
        $this->code = $code;
        $this->uri = ($social instanceof GetUrlsInterface) ? $social->getUriToken():'';
        $this->client_id = $social->clientId;
        $this->redirect_uri = $social->redirectUrl;
        $this->client_secret = $social->clientSecret;
        $this->client = $social->client;
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
        return $this->client->post($this->uri, $this->generateData(), $this->header, $this->params);
    }

}