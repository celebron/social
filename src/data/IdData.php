<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\socialSource\data;

use Celebron\common\Token;
use Celebron\socialSource\interfaces\UrlsInterface;
use Celebron\socialSource\OAuth2;
use Celebron\socialSource\responses\Id;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;
use yii\httpclient\Request as ClientRequest;
use yii\web\BadRequestHttpException;
use Yiisoft\Http\Header;

/**
 *
 * @property-read null|int $expiresIn
 * @property-read string $tokenTypeToken
 * @property-read array $tokenData
 * @property-read null|string $tokenType
 * @property-read null|string $accessToken
 * @property string $uri
 * @property-read null|string $refreshToken
 */
class IdData extends AbstractData
{
    private string $_uri = '';

    private ?ClientRequest $_request = null;

    public function __construct(
        OAuth2 $social,
        public readonly Token $token,
        array $config = [])
    {
        parent::__construct($social, $config);
        if ($social instanceof UrlsInterface) {
            $this->setUri($social->getUriInfo());
        }
    }

    /**
     * @throws BadRequestHttpException
     */
    public function getUri():string
    {
        if(empty($this->_uri)) {
            throw new BadRequestHttpException(\Yii::t('social','[{request}]Property $uri empty.',[
                'request' => 'requestId'
            ]));
        }
        return $this->_uri;
    }

    public function setUri(string $uri):void
    {
        $this->_uri = $uri;
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

    public function getTokenData(): array
    {
        return $this->token->data;
    }


    public function get(array $header = [], array $data = []): ClientRequest
    {
        return $this->_request =  $this->client->get($this->getUri(), $data, $header);
    }

    public function getHeaderOauth(array $data = [], array $header = []): ClientRequest
    {
        $header = ArrayHelper::merge([
            Header::AUTHORIZATION => 'OAuth ' . $this->getAccessToken()
        ], $header);
        return $this->get($header, $data);
    }


    public function post(array $data = [], array $header = []): ClientRequest
    {
        return $this->_request = $this->client->post($this->getUri(), $data, $header);
    }

    public function postHeaderOauth(array $data = [], array $header = [])
    {
        $header = ArrayHelper::merge([
            Header::AUTHORIZATION => 'OAuth ' . $this->getAccessToken()
        ], $header);
        return $this->post($header, $data);
    }

    public function put(?array $data, array $header = []): ClientRequest
    {
        return $this->_request =  $this->client->put($this->getUri(), $data, $header);
    }

    public function delete(?array $data, array $header = []): ClientRequest
    {
        return $this->_request = $this->client->delete($this->getUri(), $data, $header);
    }

    public function responseId(string|\Closure|array $field, ?ClientRequest $request = null, ?\Closure $handler = null): Id
    {
        $request = $this->_request
            ?? $request
            ?? throw new BadRequestHttpException(\Yii::t('social','Сlient request is incorrect'));
        $response = $this->send($request, $handler);
        return $this->social->responseId($field, $response->getData());
    }
}