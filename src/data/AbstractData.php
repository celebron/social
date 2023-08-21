<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\socialSource\data;

use Celebron\socialSource\interfaces\UrlsInterface;
use Celebron\socialSource\OAuth2;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\httpclient\Client;
use yii\httpclient\CurlTransport;
use yii\httpclient\Exception;
use yii\httpclient\Request as ClientRequest;
use yii\httpclient\Response as ClientResponse;
use yii\web\BadRequestHttpException;

abstract class AbstractData extends BaseObject
{
    public readonly Client $client;
    public string $redirect_uri;
    public string $client_id;
    public string $uri;

    public function __construct (protected readonly OAuth2 $social, array $config = [])
    {
        $this->client = new Client();
        $this->client->transport = CurlTransport::class;
        if($this->social instanceof UrlsInterface) {
            $this->client->baseUrl = $this->social->getBaseUrl();
        }
        $this->client_id = $this->social->clientId;
        $this->redirect_uri = $this->social->redirectUrl;
        parent::__construct($config);
    }

    /**
     * @throws InvalidConfigException
     * @throws Exception
     * @throws BadRequestHttpException
     */
    final public function send(ClientRequest $request, ?\Closure $handler = null) : ClientResponse
    {
        $response = $this->client->send($request);
        if($response->getIsOk()) {
            $handler ??= function ($response) {
                $data = $response->getData();
                if (!isset($data['error'])) {
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
                return $response;
            };

            return $handler->call($this, $response, $request);
        }

        throw new BadRequestHttpException(\Yii::t('social', '[{socialName}]Response not correct. Code E{statusCode}', [
            'socialName' => $this->social->socialName,
            'statusCode' => $response->getStatusCode(),
        ]));
    }

}