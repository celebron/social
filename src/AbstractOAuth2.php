<?php

namespace Celebron\social;

use Celebron\common\Token;
use Celebron\social\interfaces\BaseUrlInterface;
use Celebron\social\interfaces\OAuth2Interface;
use Celebron\social\interfaces\SocialAuthTrait;
use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\httpclient\{Client, CurlTransport, Exception, Request, Response};
use yii\base\InvalidRouteException;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;


/**
 *
 * @property string $clientId
 * @property string $redirectUrl
 * @property-read string $uriToken
 * @property-read string $uriRefreshToken
 * @property bool $active
 * @property-read string $uriInfo
 * @property-read string $uriCode
 * @property string $clientSecret
 */
abstract class AbstractOAuth2 extends Component implements OAuth2Interface
{
    use SocialAuthTrait;
    public const EVENT_DATA_CODE = 'dataCode';
    public const EVENT_DATA_TOKEN = 'dataToken';

    private ?string $_clientId = null;
    private ?string $_clientSecret = null;
    private ?string $_redirectUrl = null;
    private bool $_active = false;

    public readonly Client $client;
    public ?Token $token = null;

    public function __construct (
        protected readonly string $socialName,
        protected readonly Configuration $configure,
        array $config = []
    )
    {
        $this->client = new Client();
        $this->client->transport = CurlTransport::class;
        if($this instanceof BaseUrlInterface) {
            $this->client->baseUrl = $this->getBaseUrl();
        }
        parent::__construct($config);
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

    public function getActive (): bool
    {
        if (isset($this->config->paramsGroup, \Yii::$app->params[$this->config->paramsGroup][$this->socialName]['active'])) {
            return \Yii::$app->params[$this->config->paramsGroup][$this->socialName]['active'];
        }
        return $this->_active;
    }

    public function setActive (bool $value): void
    {
        $this->_active = $value;
    }

    /**
     * @param string|null $code
     * @param State $state
     * @return  SocialResponse
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
            0 => $this->config->route . '/handler',
            'social' => $this->socialName,
            'state' => (string)State::create($method, $state),
        ], true);
    }

    public function getRedirectUrl (): string
    {
        if(isset($this->_redirectUrl)) {
            return $this->_redirectUrl;
        }
        throw new InvalidArgumentException('RedirectUrl is null');
    }

    public function setRedirectUrl (string $url): void
    {
        $this->_redirectUrl = $url;
    }
}