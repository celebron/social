<?php

namespace Celebron\social;

use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\httpclient\{Client, CurlTransport, Exception, Request, Response};
use yii\web\BadRequestHttpException;

abstract class SocialOAuth extends Social
{

    public string $clientId;
    public string $clientSecret;

    public string $clientUrl = '';
    private ?Client $_client = null;

    public function rules (): array
    {
        return ArrayHelper::merge(parent::rules(),[
            [['clientUrl'], 'url'],
            [['clientId', 'clientSecret'], 'string'],
            [['clientId', 'clientSecret', 'clientUrl'], 'required'],
        ]);
    }

    /**
     * CurlClient
     * @return Client
     */
    final public function getClient (): Client
    {
        if ($this->_client === null) {
            $this->_client = new Client();
            $this->_client->transport = CurlTransport::class;
        }
        $this->_client->baseUrl = $this->clientUrl;
        return $this->_client;
    }

    /**
     * @param string $url
     * @param array $data
     * @return Request
     */
    public function getCodeUrl(string $url, array $data=[]) : Request
    {
        $urlQuery = ArrayHelper::merge([
            0 => $url,
            'redirect_uri' => $this->redirectUrl,
            'state' => $this->state,
            'response_type' => 'code',
            'client_id' => $this->clientId,
        ], $data);
        return $this->getClient()->get($urlQuery);
    }

    /**
     * @param array $data
     * @return array
     */
    public function getTokenData(array $data): array
    {
        return ArrayHelper::merge([
            'redirect_uri' => $this->redirectUrl,
            'grant_type' => 'authorization_code',
            'code' => $this->code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ], $data);
    }


    /**
     * @param string $url
     * @param array $data
     * @param array $headers
     * @param array $params
     * @return Response
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function getToken(string $url, array $data=[], array $headers=[], array $params=[]): Response
    {
        $sendUrl = $this->getClient()->post($url, $this->getTokenData($data), $headers, $params);
        return $this->send($sendUrl,'token');
    }

    /**
     * @param string $url
     * @param array $data
     * @return void
     */
    public function getCode(string $url, array $data=[]): void
    {
        $this->redirect($this->getCodeUrl($url, $data));
    }


    /**
     * @param Request $sender
     * @param Response $response
     * @param string|null $theme
     * @return mixed
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws InvalidConfigException
     */
    protected function send(Request $sender, string $theme = 'info') : Response
    {
        $response = $this->getClient()->send($sender);
        if ($response->isOk) {
            $this->data[$theme] = $response->getData();
            return $response;
        }

        $this->getException($response);
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    protected function sendRedirect(Request $sender): void
    {
        $response = $this->getClient()->send($sender);
         if ($response->isOk) {
             $this->redirect($sender);
             return;
         }

        $this->getException($response);
    }

    /**
     * @param Response $response
     * @throws BadRequestHttpException
     * @throws Exception
     */
    protected function getException (Response $response): void
    {
        $data = $response->getData();
        if (isset($data['error'], $data['error_description'])) {
            throw new BadRequestHttpException('[' . static::socialName() . "]Error {$data['error']} (E{$response->getStatusCode()}). {$data['error_description']}");
        }
        throw new BadRequestHttpException('[' . static::socialName() . "]Response not correct. Code E{$response->getStatusCode()}");
    }

}
