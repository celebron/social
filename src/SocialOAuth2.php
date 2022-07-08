<?php

namespace Celebron\social;

use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\httpclient\{Client, CurlTransport, Exception, Request, Response};
use yii\web\BadRequestHttpException;

/**
 * Оавторизация Oauth2
 */
abstract class SocialOAuth2 extends Social
{

    /** @var string - ид от соц.сети */
    public string $clientId;
    /** @var string - секрет от соц.сети  */
    public string $clientSecret;
    /** @var string - url api соц.сети */
    public string $clientUrl = '';
    private ?Client $_client = null;

    /**
     * @return array
     */
    public function rules (): array
    {
        return ArrayHelper::merge(parent::rules(),[
            [['clientUrl'], 'url'],
            [['clientId', 'clientSecret'], 'string'],
            [['clientId', 'clientSecret'], 'required'],
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
     * формировниие ссылки на получения кода
     * @param string $url
     * @param array $data
     * @return Request
     */
    public function getCodeUrl(string $url, array $data=[]) : Request
    {
        $urlQuery = ArrayHelper::merge([
            0 => $url,
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUrl,
            'state' => $this->state,
        ], $data);
        return $this->getClient()->get($urlQuery);
    }

    /**
     * Формирования даты для полуния токена
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
     * Получения токена
     * @param string $url - ссылка
     * @param array $data - данные
     * @param array $headers - заголовки
     * @param array $params - параметры
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
     * Редирект на получении кода
     * @param string $url
     * @param array $data
     * @return void
     */
    public function getCode(string $url, array $data=[]): void
    {
        $this->redirect($this->getCodeUrl($url, $data));
    }


    /**
     * Выполнение отправки сообщения
     * @param Request $sender - Запрос
     * @param string $theme - Тема
     * @return Response
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws InvalidConfigException
     */
    protected function send(Request $sender, string $theme = 'info') : Response
    {
        $response = $this->getClient()->send($sender);
        if ($response->isOk && !isset($response->data['error'])) {

            $this->data[$theme] = $response->getData();
            \Yii::debug($this->data[$theme],static::class);
            return $response;
        }

        $this->getException($response);
    }

    /**
     * Перенаправление
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
     * Отслеживание ошибки
     * @param Response $response
     * @throws BadRequestHttpException
     * @throws Exception
     */
    protected function getException (Response $response): void
    {
        $data = $response->getData();
        \Yii::warning($this->data, static::class);
        if (isset($data['error'], $data['error_description'])) {
            throw new BadRequestHttpException('[' . static::socialName() . "]Error {$data['error']} (E{$response->getStatusCode()}). {$data['error_description']}");
        }
        throw new BadRequestHttpException('[' . static::socialName() . "]Response not correct. Code E{$response->getStatusCode()}");
    }

}
