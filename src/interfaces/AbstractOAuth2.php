<?php

namespace Celebron\social\interfaces;

use Celebron\common\Token;
use Celebron\social\Configuration;
use Celebron\social\RequestCode;
use Celebron\social\RequestId;
use Celebron\social\RequestToken;
use Celebron\social\SocialResponse;
use Celebron\social\State;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\base\InvalidRouteException;
use yii\console\ExitCode;
use yii\helpers\Url;
use yii\httpclient\{Client, CurlTransport, Exception, Request, Response};
use yii\web\BadRequestHttpException;

/**
 * @property bool $active
 * @property string $clientId
 * @property string $clientSecret
 * @property string $redirectUrl
 * @property-read string $uriToken
 * @property-read string $uriRefreshToken
 * @property-read string $uriInfo
 * @property-read string $uriCode
 */
abstract class AbstractOAuth2 extends AbstractSocialAuth implements OAuth2Interface
{
    public const EVENT_DATA_CODE = 'dataCode';
    public const EVENT_DATA_TOKEN = 'dataToken';

    public readonly Client $client;
    public ?Token $token = null;

    public function __construct (string $socialName, Configuration $configure, $config = [])
    {
        $this->client = new Client();
        $this->client->transport = CurlTransport::class;
        if($this instanceof UrlBaseInterface) {
            $this->client->baseUrl = $this->getBaseUrl();
        }
        parent::__construct($socialName, $configure, $config);
    }

    /**
     * @param string|null $code
     * @param State $state
     * @return SocialResponse
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws InvalidConfigException
     * @throws InvalidRouteException
     * @throws \Exception
     */
    public function request(?string $code, State $state): SocialResponse
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
            if ($this instanceof FullUrlInterface) {
                $url->setFullUrl($this->setFullUrl($url));
            }

            //Перейти на соответсвующую страницу
            \Yii::$app->response->redirect($url->getFullUrl(), checkAjax: false)->send();
            exit(ExitCode::OK);
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
            throw new BadRequestHttpException('Random not equal');
        }

        $request = new RequestId($this);
        $response = $this->requestId($request);

        \Yii::debug("Userid: {$response->getId()}.", static::class);
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
    public function sendResponse(Request $sender, string|\Closure|array $field) : SocialResponse
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