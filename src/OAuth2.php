<?php

namespace Celebron\socialSource;

use Celebron\common\Token;
use Celebron\socialSource\behaviors\OAuth2Behavior;
use Celebron\socialSource\behaviors\ViewerBehavior;
use Celebron\socialSource\interfaces\OAuth2Interface;
use Celebron\socialSource\interfaces\UrlFullInterface;
use Celebron\socialSource\interfaces\UrlsInterface;
use Celebron\socialSource\interfaces\ViewerInterface;
use Celebron\socialSource\requests\CodeRequest;
use Celebron\socialSource\requests\IdRequest;
use Celebron\socialSource\requests\TokenRequest;
use yii\base\InvalidConfigException;
use yii\base\InvalidRouteException;
use yii\console\Application;
use yii\console\ExitCode;
use yii\helpers\Url;
use yii\httpclient\Client;
use yii\httpclient\CurlTransport;
use yii\httpclient\Exception;
use yii\httpclient\Response as ClientResponse;
use yii\httpclient\Request as ClientRequest;
use yii\web\BadRequestHttpException;
use yii\web\Session;

/**
 * @property string $clientSecret
 * @property string $clientId
 * @property string $redirectUrl - устанавливать если используется консоль (в остальных случаях автогенерация)
 * @property bool $active
 */
abstract class OAuth2 extends Social implements OAuth2Interface
{
    public const EVENT_DATA_CODE = 'dataCode';
    public const EVENT_DATA_TOKEN = 'dataToken';

    public Token $token;

    public readonly Client $client;

    public function __construct (string $socialName, Configuration $configure, $config = [])
    {
        $this->client = new Client();
        $this->client->transport = CurlTransport::class;
        if($this instanceof UrlsInterface) {
            $this->client->baseUrl = $this->getBaseUrl();
        }
        parent::__construct($socialName, $configure, $config);
    }

    public function behaviors ()
    {
        $behaviors = parent::behaviors();
        $behaviors[OAuth2Interface::class] = new OAuth2Behavior($this->socialName, $this->configure);
        if($this instanceof ViewerInterface) {
            $behaviors[ViewerInterface::class] = new ViewerBehavior($this->socialName, $this->configure);
        }
        return $behaviors;
    }

    /**
     * @throws BadRequestHttpException
     */
    public function urlForCode(State $state):string
    {
        $request = new CodeRequest($this, $state);
        $this->requestCode($request);
        $url = $this->client->get($request->generateUri());
        if ($this instanceof UrlFullInterface) {
            $url->setFullUrl($this->fullUrl($url));
        }
        return $url->getFullUrl();
    }

    /**
     * @throws InvalidRouteException
     * @throws InvalidConfigException
     * @throws Exception
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function request (?string $code, State $state): ?ResponseSocial
    {
        $session = \Yii::$app->get('session', false) ?? [];
        if ($session instanceof Session && !$session->isActive) {
            $session->open();
        }

        if ($code === null) {
            $session['social_random'] = $state->random;
            \Yii::$app->response->redirect($this->urlForCode($state), checkAjax: false)->send();
            exit(ExitCode::OK);
        }

        $equalRandom = true;
        if ($session instanceof Session) {
            $equalRandom = $state->equalRandom($session['social_random']);
            \Yii::$app->session->remove('social_random');
        }

        if ($equalRandom) {
            $request = new TokenRequest($code, $this);
            $this->requestToken($request);
            if ($request->send) {
                $this->token = $this->sendToken($request);
            }
        } else {
            throw new BadRequestHttpException(\Yii::t('social','Random not equal'));
        }

        $request = new IdRequest($this);
        $response = $this->requestId($request);

        \Yii::debug("Userid: {$response->getId()}.", static::class);
        return $response;
    }

    /**
     * @throws InvalidConfigException
     * @throws Exception
     * @throws BadRequestHttpException
     */
    final protected function send(ClientRequest $sender) : ClientResponse
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
    final public function sendToken(TokenRequest $sender) : Token
    {
        $data = $this
            ->send($sender->sender())
            ->getData();
        return new Token($data);
    }

    /**
     * @param ClientRequest $sender
     * @param string|\Closure|array $field
     * @return mixed
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function sendResponse(ClientRequest $sender, string|\Closure|array $field) : ResponseSocial
    {
        $response = $this->send($sender);
        return $this->response($field, $response->getData());
    }

    public function url(string $method, ?string $state = null):string
    {
        return Url::toRoute([
            0 => $this->configure->route . '/handler',
            'social' => $this->socialName,
            'state' => (string)State::create($method, $state),
        ], true);
    }
}