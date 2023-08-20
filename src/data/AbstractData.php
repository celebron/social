<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social\data;

use Celebron\source\social\interfaces\UrlsInterface;
use Celebron\source\social\OAuth2;
use yii\base\BaseObject;
use yii\httpclient\Client;
use yii\httpclient\CurlTransport;
use yii\httpclient\Request as ClientRequest;
use yii\httpclient\Response as ClientResponse;
use yii\web\BadRequestHttpException;

/**
 *
 * @property string $uri
 */
abstract class AbstractData extends BaseObject
{
    public readonly Client $client;
    public string $redirect_uri;
    public string $client_id;

    public function __construct (protected readonly OAuth2 $social, array $config = [])
    {
        $this->client = new Client();
        $this->client->transport = CurlTransport::class;
        if($this->social instanceof UrlsInterface) {
            $this->client->baseUrl = $this->social->getBaseUrl();
        }
        $this->client_id = $this->social->getClientId();
        $this->redirect_uri = $this->social->getRedirectUrl();
        parent::__construct($config);
    }

    final public function send(ClientRequest $request, ?\Closure $handler = null) : ClientResponse
    {
        $response = $this->client->send($request);

        if ($handler === null) {
            $data = $response->getData();
            if ($response->isOk && !isset($data['error'])) {
                return $response;
            }

            if (isset($data['error'], $data['error_description'])) {
                throw new BadRequestHttpException(\Yii::t('social', '[{socialName}]Error {error} E{statusCode}. {description}', [
                    'socialName' => $this->social->socialName,
                    'statusCode' => $response->getStatusCode(),
                    'description' => $data['error_description'],
                    'error' => $data['error'],
                ]));
            }

            throw new BadRequestHttpException(\Yii::t('social', '[{socialName}]Response not correct. Code E{statusCode}', [
                'socialName' => $this->social->socialName,
                'statusCode' => $response->getStatusCode(),
            ]));
        }

        return $handler($response, $request);
    }

    private ?string $_uri = null;
    public function getUri():string
    {
        if(empty($this->_uri)) {
            throw new BadRequestHttpException(\Yii::t('social','[{request}]Property $uri empty.',[
                'request' => static::class
            ]));
        }
        return $this->_uri;
    }

    public function setUri(string $value):void
    {
        $this->_uri = $value;
    }

}