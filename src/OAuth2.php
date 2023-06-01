<?php

namespace Celebron\social;

use Celebron\common\Token;
use Celebron\social\interfaces\SetFullUrlInterface;
use Celebron\social\interfaces\GetUrlsInterface;
use yii\base\InvalidConfigException;
use yii\httpclient\{Client, CurlTransport, Exception, Request, Response};
use yii\base\InvalidRouteException;
use yii\web\BadRequestHttpException;


abstract class OAuth2 extends AuthBase
{
    public string $clientId;
    public string $clientSecret;
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

    public function __construct (SocialConfiguration $config, array $cfg = [])
    {
        parent::__construct($config, $cfg);
        $this->client = new Client();
        $this->client->transport = CurlTransport::class;
        if($this instanceof GetUrlsInterface) {
            $this->client->baseUrl = $this->getBaseUrl();
        }
    }

    /**
     * @param string|null $code
     * @param State $state
     * @return Response
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
            if ($request->send) {
                $this->token = $this->sendToken($request);
            }
        } else {
            throw new BadRequestHttpException('Random not equal', code: 1);
        }

        $response = $this->requestId(new RequestId($this));
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
            throw new BadRequestHttpException('[' . static::socialName() . "]Error {$data['error']} (E{$response->getStatusCode()}). {$data['error_description']}");
        }
        throw new BadRequestHttpException('[' . static::socialName() . "]Response not correct. Code E{$response->getStatusCode()}");
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
    protected function sendResponse(Request $sender, string|\Closure|array $field) : \Celebron\social\Response
    {
        $response = $this->send($sender);
        return $this->response($field, $response->getData());
    }



}