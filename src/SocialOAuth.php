<?php

namespace Celebron\social;

use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\httpclient\{Exception, Request, Response};
use yii\web\BadRequestHttpException;

abstract class SocialOAuth extends SocialBase
{

    public ?string $clientId = null;
    public ?string $clientSecret = null;

    /**
     * @throws InvalidConfigException
     */
    public function init ()
    {
       if(($this->clientId === null) || $this->clientSecret === null) {
           throw new InvalidConfigException('Not set $clientId and(or) $clientSecret',0);
       }
    }

    /**
     * @param string $url
     * @param string $state
     * @param array $data
     * @return Request
     */
    public function getCodeUrl(string $url, string $state, array $data=[]) : Request
    {
        $data_merge = [
            0 => $url,
            'redirect_uri' => $this->redirectUrl,
            'state' => $state,
            'response_type' => 'code',
            'client_id' => $this->clientId,
        ];

        $urlQuery = ArrayHelper::merge($data_merge, $data);
        return $this->getClient()->get($urlQuery);
    }

    /**
     * @param string $code
     * @param array $data
     * @return array
     */
    public function getTokenData(string $code, array $data = []): array
    {
        $data_merge = [
            'redirect_uri' => $this->redirectUrl,
            'grant_type' => 'authorization_code',
            'code'=>$code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ];
        return ArrayHelper::merge($data_merge, $data);
    }

    /**
     * @param string $url
     * @param string $code
     * @param array $data
     * @param array $headers
     * @param array $params
     * @return mixed
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function getToken(string $url, string $code, array $data=[], array $headers=[], array $params=[]): Response
    {
        $sendUrl = $this->getClient()->post($url, $this->getTokenData($code, $data), $headers, $params);
        return $this->send($sendUrl,'token');
    }

    public function getCode($url, $state, $data=[]): void
    {
        $this->redirect($this->getCodeUrl($url, $state,$data));
    }

    /**
     * @param Request $sender
     * @param string $theme
     * @param bool $throw
     * @return Response
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws Exception
     */
    protected function send(Request $sender, string $theme, bool $throw = false) : Response
    {
        $response = $this->getClient()->send($sender);
        if ($response->isOk) {
            $this->data[$theme] = $response->getData();
        } elseif($throw) {
            $this->getException($response);
        }
        return $response;
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
            throw new BadRequestHttpException('[' . static::getSocialName() . "]Error {$data['error']} (E{$response->getStatusCode()}). {$data['error_description']}");
        }
        throw new BadRequestHttpException('[' . static::getSocialName() . "]Response not correct. Code E{$response->getStatusCode()}");
    }

}
