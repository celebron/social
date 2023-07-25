<?php

namespace Celebron\social;

use Celebron\common\Token;
use Celebron\social\interfaces\AbstractOAuth2;
use Celebron\social\interfaces\GetUrlsInterface;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;
use yii\httpclient\Request;
use yii\web\BadRequestHttpException;
use Yiisoft\Http\Header;

/**
 *
 * @property-read null|int $expiresIn
 * @property-read null|string $tokenType
 * @property-read null|string $accessToken
 * @property-read string $tokenTypeToken
 * @property-read array $tokenData
 * @property string $uri
 * @property-read null|string $refreshToken
 */
class RequestId extends BaseObject
{

    private string $_uri = '';

    public readonly Token $token;
    private readonly Client $client;

    public function __construct(AbstractOAuth2 $social, array $config = [])
    {
        parent::__construct($config);
        if ($social instanceof GetUrlsInterface) {
            $this->setUri($social->getUriInfo());
        }
        $this->token = $social->token;
        $this->client = $social->client;
    }

    /**
     * @throws BadRequestHttpException
     */
    public function getUri():string
    {
        if(empty($this->_uri)) {
            throw new BadRequestHttpException('[RequestId] Property $uri empty');
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

    /**
     * Гет запрос
     * @param array $header
     * @param array $data
     * @return Request
     * @throws BadRequestHttpException
     */
    public function get(array $header = [], array $data = []): Request
    {
        return  $this->client->get($this->getUri(), $data, $header);
    }


    /**
     * @param array $data
     * @param array $header
     * @return Request
     */
    public function getHeaderOauth(array $data = [], array $header = []): Request
    {
        $header = ArrayHelper::merge([
            Header::AUTHORIZATION => 'OAuth ' . $this->getAccessToken()
        ], $header);
        return $this->get($header, $data);
    }

    /**
     * @param array $data
     * @param array $header
     * @return Request
     * @throws BadRequestHttpException
     */
    public function post(array $data = [], array $header = []): Request
    {
        return $this->client->post($this->getUri(), $data, $header);
    }

    /**
     * @param array $data
     * @param array $header
     * @return Request
     */
    public function postHeaderOauth(array $data = [], array $header = []): Request
    {
        $header = ArrayHelper::merge([
            Header::AUTHORIZATION => 'OAuth ' . $this->getAccessToken()
        ], $header);
        return $this->post($header, $data);
    }

    /**
     * @throws BadRequestHttpException
     */
    public function put(?array $data, array $header = []): Request
    {
        return $this->client->put($this->getUri(), $data, $header);
    }

    /**
     * @throws BadRequestHttpException
     */
    public function delete(?array $data, array $header = []): Request
    {
        return $this->client->delete($this->getUri(), $data, $header);
    }

}