<?php

namespace Celebron\social;

use yii\httpclient\Client;
use yii\httpclient\Request;
use Yiisoft\Http\Header;

/**
 *
 * @property-read null|int $expiresIn
 * @property-read null|string $tokenType
 * @property-read null|string $accessToken
 * @property-read array $data
 * @property-read string $tokenTypeToken
 * @property-read null|string $refreshToken
 */
class RequestId extends \yii\base\BaseObject
{

    public string $uri;

    public readonly Token $token;
    private readonly Client $client;

    public function __construct(Social $social, array $config = [])
    {
        parent::__construct($config);
        $this->token = $social->token;
        $this->client = $social->client;
    }

    public function getAccessToken() : ?string
    {
        return $this->token->accessToken;
    }

    public function getExpiresIn(): ?int
    {
        return $this->token->expiresIn;
    }

    public function getRefreshToken():?string
    {
        return $this->token->expiresIn;
    }

    public function getTokenType():?string
    {
        return $this->token->tokenType;
    }

    public function getTokenTypeToken():string
    {
        return $this->getTokenType() . ' ' . $this->getAccessToken();
    }

    public function getData(): array
    {
        return $this->token->data;
    }

    /**
     * Гет запрос
     * @param array $header
     * @param array $data
     * @return Request
     */
    public function get(array $header = [], array $data = []): Request
    {
        return  $this->client->get($this->uri, $data, $header);
    }


    /**
     * @param array $data
     * @return Request
     */
    public function getHeaderOauth(array $data = []): Request
    {
        return $this->get([ Header::AUTHORIZATION => 'OAuth ' . $this->getAccessToken()], $data);
    }

    /**
     * @param array $data
     * @param array $header
     * @return Request
     */
    public function post(array $data = [], array $header = []): Request
    {
        return $this->client->post($this->uri, $data, $header);
    }

    /**
     * @param array $data
     * @return Request
     */
    public function postHeaderOauth(array $data = []): Request
    {
        return $this->post([ Header::AUTHORIZATION => 'OAuth ' . $this->getAccessToken()], $data);
    }

    public function put(?array $data, array $header = []): Request
    {
        return $this->client->put($this->uri, $data, $header);
    }

    public function delete(?array $data, array $header = []): Request
    {
        return $this->client->delete($this->uri, $data, $header);
    }

}