<?php

namespace Celebron\social;

use yii\helpers\ArrayHelper;
use yii\httpclient\Client;
use yii\httpclient\Request;
use yii\httpclient\Response;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
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


    public function __construct(
        public ?Token $token,
        public Client $client,
        array $config = []
    )
    {
        parent::__construct($config);
        if(empty($this->token)) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @throws BadRequestHttpException
     */
    public function getAccessToken() : ?string
    {
        return $this->getValue('access_token');
    }

    /**
     * @throws BadRequestHttpException
     */
    public function getExpiresIn(): ?int
    {
        return $this->getValue('expires_in');
    }

    public function getRefreshToken():?string
    {
        return $this->getValue('refresh_token');
    }

    public function getTokenType():?string
    {
        return $this->getValue('token_type');
    }

    public function getTokenTypeToken():string
    {
        return $this->getTokenType() . ' ' . $this->getAccessToken();
    }

    public function getData():array
    {
        if($this->response->isOk && empty($this->response->data['error'])) {
            return $this->response->data;
        }

        if(isset($this->response->data['error_description'],$this->response->data['error'])) {
            throw new BadRequestHttpException(
                "[{$this->response->data['error']}] {$this->response->data['error_description']}"
            );
        }

        throw new BadRequestHttpException($this->response->data['error']);
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
     * @throws BadRequestHttpException
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
     * @throws BadRequestHttpException
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

    protected function getValue(string $key): mixed
    {
        if($this->response->isOk && empty($this->response->data['error'])) {
            return ArrayHelper::getValue($this->response->data, $key);
        }

        if(isset($this->response->data['error_description'],$this->response->data['error'])) {
            throw new BadRequestHttpException(
                "[{$this->response->data['error']}] {$this->response->data['error_description']}"
            );
        }

        throw new BadRequestHttpException($this->response->data['error']);
    }
}