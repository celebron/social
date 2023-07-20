<?php

namespace Celebron\social;

use Celebron\common\Token;
use Celebron\social\interfaces\SetFullUrlInterface;
use Celebron\social\interfaces\GetUrlsInterface;
use yii\base\InvalidConfigException;
use yii\httpclient\{Client, CurlTransport, Exception, Request, Response};
use yii\base\InvalidRouteException;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;


/**
 *
 * @property string $clientId
 * @property string $redirectUrl
 * @property string $clientSecret
 */
abstract class OAuth2 extends AuthBase
{
    public const EVENT_DATA_CODE = 'dataCode';
    public const EVENT_DATA_TOKEN = 'dataToken';

    private ?string $_clientId = null;
    private string $_clientSecret;
    public string $redirectUrl;

    public readonly Client $client;

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

    abstract public function requestId(RequestId $request): \Celebron\social\Response;

    public function __construct (
        string        $socialName,
        Configuration $config,
        array         $cfg = [])
    {
        $this->client = new Client();
        $this->client->transport = CurlTransport::class;
        if($this instanceof GetUrlsInterface) {
            $this->client->baseUrl = $this->getBaseUrl();
        }
        parent::__construct($socialName, $config, $cfg);
    }

    /**
     * @throws InvalidConfigException
     */
    public function getClientId():string
    {
        if(empty($this->_clientId)) {
            if(isset($this->config->paramsGroup, \Yii::$app->params[$this->config->paramsGroup][$this->socialName]['clientId'])) {
               return \Yii::$app->params[$this->config->paramsGroup][$this->socialName]['clientId'];
            }
            throw new InvalidConfigException('Not param "clientId" to social "' . $this->socialName. '"');
        }

        return $this->_clientId;
    }
    public function setClientId(string $value): void
    {
        $this->_clientId = $value;
    }
    public function getClientSecret() : string
    {
        if(empty($this->_clientSecret)) {
            if(isset($this->config->paramsGroup, \Yii::$app->params[$this->config->paramsGroup][$this->socialName]['clientSecret'])) {
                return \Yii::$app->params[$this->config->paramsGroup][$this->socialName]['clientSecret'];
            }
            throw new InvalidConfigException('Not param "clientSecret" to social "' . $this->socialName. '"');
        }

        return $this->_clientSecret;
    }

    public function setClientSecret(string $value): void
    {
        $this->_clientSecret = $value;
    }

    /**
     * @param string|null $code
     * @param State $state
     * @return  \Celebron\social\Response
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws InvalidConfigException
     * @throws InvalidRouteException
     */
    public function request(?string $code, State $state): \Celebron\social\Response
    {
        $session = \Yii::$app->session;
        if (!$session->isActive) {
            $session->open();
        }

        if ($code === null) {
            $request = new RequestCode($this, $state);
            $this->requestCode($request);
            if(empty($request->uri)) {
                throw new BadRequestHttpException('[RequestCode] Empty URI');
            }

            $session['social_random'] = $request->state->random;
            $url = $this->client->get($request->generateUri());
            if ($this instanceof SetFullUrlInterface) {
                $url->setFullUrl($this->setFullUrl($url));
            }

            //Перейти на соответсвующую страницу
            \Yii::$app->response->redirect($url->getFullUrl(), checkAjax: false)->send();
            exit(0);
        }

        $equalRandom = $state->equalRandom($session['social_random']);
        \Yii::$app->session->remove('social_random');

        if ($equalRandom) {
            $request = new RequestToken($code, $this);
            $this->requestToken($request);
            if(empty($request->uri)) {
                throw new BadRequestHttpException('[RequestToken] Empty request URI',1);
            }

            if ($request->send) {
                $this->token = $this->sendToken($request);
            }
        } else {
            throw new BadRequestHttpException('Random not equal');
        }

        $request = new RequestId($this);
        $response = $this->requestId($request);
        if(empty($request->uri)) {
            throw new BadRequestHttpException('[RequestId] Empty request URI',2);
        }

        \Yii::debug("Userid: {$response->id}.", static::class);
        return  $response;
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    final protected function send(Request $sender) : Response
    {
        $response = $this->client->send($sender);
        $data = $response->getData();
        if ($response->isOk && !isset($data['error'])) {
            return $response;
        }

        if (isset($data['error'], $data['error_description'])) {
            throw new BadRequestHttpException('[' . $this->socialName . "]Error {$data['error']} (E{$response->getStatusCode()}). {$data['error_description']}");
        }
        throw new BadRequestHttpException('[' . $this->socialName . "]Response not correct. Code E{$response->getStatusCode()}");
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    final public function sendToken(RequestToken $sender) : Token
    {
        //Получаем данные
        $data = $this
            ->send($sender->sender())
            ->getData();
        return new Token($data);
    }

    /**
     * @param Request $sender
     * @param string|\Closure|array $field
     * @return mixed
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws InvalidConfigException
     * @throws \Exception
     */
    public function sendResponse(Request $sender, string|\Closure|array $field) : \Celebron\social\Response
    {
        $response = $this->send($sender);
        return $this->response($field, $response->getData());
    }

    public function url(string $method, ?string $state = null):string
    {
        return Url::toRoute([
            0 => $this->config->route . '/handler',
            'social' => $this->socialName,
            'state' => (string)State::create($method, $state),
        ], true);
    }
}