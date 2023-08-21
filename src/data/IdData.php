<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social\data;

use Celebron\common\Token;
use Celebron\source\social\interfaces\UrlsInterface;
use Celebron\source\social\OAuth2;
use Celebron\source\social\responses\Id;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;
use yii\httpclient\Request as ClientRequest;
use yii\web\BadRequestHttpException;
use Yiisoft\Http\Header;

/**
 *
 * @property-read string $typedToken
 */
class IdData extends AbstractData
{
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

    public function getTypedToken():string
    {
        return $this->token->getTokenType() . ' ' . $this->token->getAccessToken();
    }

    public function get(array $header = [], array $data = []): ClientRequest
    {
        return $this->_request =  $this->client->get($this->getUri(), $data, $header);
    }

    public function getHeaderOauth(array $data = [], array $header = []): ClientRequest
    {
        $header = ArrayHelper::merge([
            Header::AUTHORIZATION => 'OAuth ' . $this->token->getAccessToken()
        ], $header);
        return $this->get($header, $data);
    }


    public function post(array $data = [], array $header = []): ClientRequest
    {
        return $this->_request = $this->client->post($this->getUri(), $data, $header);
    }

    public function postHeaderOauth(array $data = [], array $header = []): ClientRequest
    {
        $header = ArrayHelper::merge([
            Header::AUTHORIZATION => 'OAuth ' . $this->token->getAccessToken()
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