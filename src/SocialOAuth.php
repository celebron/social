<?php

namespace Celebron\social;

use GuzzleHttp\Exception\BadResponseException;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\helpers\ArrayHelper;
use yii\httpclient\Request;
use yii\httpclient\Response;
use yii\web\BadRequestHttpException;

abstract class SocialOAuth extends SocialBase
{

    public string $clientId;

    public string $clientSecret;

    /**
     * @param string $url
     * @param array $data
     * @return Request
     */
    public function getCodeUrl(string $url, array $data=[]) : Request
    {
        $data_merge = [
            0 => $url,
            'redirect_uri' => $this->redirectUrl,
            'state' => $this->state,
            'response_type' => 'code',
            'client_id' => $this->clientId,
        ];

        $urlQuery = ArrayHelper::merge($data_merge, $data);
        return $this->getClient()->get($urlQuery);
    }

    /**
     * @param $code
     * @param $data
     * @return array
     */
    public function getTokenData($code, $data = []): array
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
     * @param $url
     * @param string $code
     * @param array $data
     * @param array $headers
     * @param array $params
     * @return mixed
     */
    public function getToken($url, string $code, array $data=[], array $headers=[], array $params=[]): Response
    {
        $sendUrl = $this->getClient()->post($url, $this->getTokenData($code, $data), $headers, $params);
        return $this->send($sendUrl,'token');
    }

    protected function send(Request $sender, string $theme, bool $throw = false) : Response
    {
        $response = $this->getClient()->send($sender);
        if ($response->isOk) {
            $this->data[$theme] = $response->getData();
        } elseif($throw) {
            //"[Yandex]Ошибка: {$data['error']}. {$data['error_description']}"
            throw new BadRequestHttpException();
        }
        return $response;
    }

}