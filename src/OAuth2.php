<?php

namespace Celebron\social;

use Celebron\social\interfaces\GetUrlsInterface;
use Celebron\social\interfaces\RequestInterface;
use yii\base\InvalidConfigException;
use yii\httpclient\{Client, CurlTransport, Exception, Request, Response};
use yii\web\BadRequestHttpException;


abstract class OAuth2 extends AuthBase implements RequestInterface
{
    public string $clientId;
    public string $clientSecret;
    public string $redirectUrl;


    public readonly Client $client;


    protected array $data = [];
    public ?Token $token = null;

    /**
     * @param RequestCode $request
     * @return void
     */
    abstract public function requestCode(RequestCode $request):void;

    /**
     * @param RequestToken $request
     * @return void
     */
    abstract public function requestToken(RequestToken $request): void;


    public function __construct ($config = [])
    {
        parent::__construct($config);
        $this->client = new Client();
        $this->client->transport = CurlTransport::class;
        if($this instanceof GetUrlsInterface) {
            $this->client->baseUrl = $this->getBaseUrl();
        }
    }

    /**
     * @throws InvalidConfigException
     * @throws Exception
     * @throws \yii\base\Exception
     * @throws BadRequestHttpException
     */
    public function Request(\ReflectionMethod $method, SocialController $controller):void
    {
        $attributes = $method->getAttributes(OAuth2Request::class);
        if (isset($attributes[0])) {
            /** @var OAuth2Request $attr */
            $attr = $attributes[0]->newInstance();
            $attr->request($this, $controller->getCode(), $controller->getState());
        }
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    final protected function send(Request $sender, string $theme = 'info') : Response
    {
        $response = $this->client->send($sender);
        if ($response->isOk && !isset($response->data['error'])) {
            $this->data[$theme] = $response->getData();
            \Yii::debug($this->data[$theme],static::class);
            return $response;
        }

        $this->getException($response);
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    final protected function sendToken(RequestToken $sender) : Token
    {
        //Получаем данные
        $data = $this
            ->send($sender->sender(), 'token')
            ->getData();
        return new Token($data);
    }

    /**
     * @throws Exception
     * @throws BadRequestHttpException
     */
    protected function getException (Response $response): void
    {
        $data = $response->getData();
        if (isset($data['error'], $data['error_description'])) {
            throw new BadRequestHttpException('[' . static::socialName() . "]Error {$data['error']} (E{$response->getStatusCode()}). {$data['error_description']}");
        }
        throw new BadRequestHttpException('[' . static::socialName() . "]Response not correct. Code E{$response->getStatusCode()}");
    }

    /**
     * Ссылка на oauth2 авторизацию
     * @param string $method
     * @param string|null $state
     * @return string
     */
    final public static function url(string $method, ?string $state = null) : string
    {
        return SocialConfiguration::url(static::socialName(), $method, $state);
    }
}