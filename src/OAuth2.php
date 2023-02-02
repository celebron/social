<?php

namespace Celebron\social;

use Celebron\social\eventArgs\ErrorEventArgs;
use Celebron\social\eventArgs\ResultEventArgs;
use Celebron\social\interfaces\GetUrlsInterface;
use Celebron\social\interfaces\SetFullUrlInterface;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\httpclient\Client;
use yii\httpclient\CurlTransport;
use yii\httpclient\Exception;
use yii\httpclient\Request;
use yii\httpclient\Response;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;


abstract class OAuth2 extends Model
{
    public const EVENT_ERROR = "error";
    public const EVENT_SUCCESS = 'success';
    public const EVENT_FAILED = 'failed';

    public string $clientId;
    public string $clientSecret;
    public string $redirectUrl;
    public bool $active = true;

    public readonly Client $client;


    protected array $data = [];
    public ?Token $token = null;

    public function __construct ($config = [])
    {
        parent::__construct($config);
        $this->client = new Client();
        $this->client->transport = CurlTransport::class;
        if($this instanceof GetUrlsInterface) {
            $this->client->baseUrl = $this->getBaseUrl();
        }

        $name = static::socialName();
        //Генерация констант под каждую соц.сеть
        $contName = 'SOCIAL_' . strtoupper($name);
        if(!defined($contName)) {
            define($contName, strtolower($name));
        }
    }

    /**
     * @throws InvalidConfigException
     * @throws Exception
     * @throws \yii\base\Exception
     * @throws BadRequestHttpException
     */
    final public function request(?string $code, State $state): void
    {
        $session = \Yii::$app->session;
        if(!$session->isActive) { $session->open(); }

        if($code === null) {
            $request = new RequestCode($this, $state);
            $this->requestCode($request);
            $session['social_random'] = $request->state->random;
            $url = $this->client->get($request->generateUri());
            if($this instanceof SetFullUrlInterface) {
                $url->setFullUrl($this->setFullUrl($url));
            }
            //Перейти на соответвующую страницу
            \Yii::$app->response->redirect($url->getFullUrl(), checkAjax: false)->send();
            exit(0);
        }

        $equalRandom = $state->equalRandom($session['social_random']);
        \Yii::$app->session->remove('social_random');

        if($equalRandom) {
            $request = new RequestToken($code, $this);
            $this->requestToken($request);
            $this->token = $this->sendToken($request);
        }
    }

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


    public function success(SocialController $action): mixed
    {
        $eventArgs = new ResultEventArgs($action);
        $this->trigger(self::EVENT_SUCCESS, $eventArgs);
        return $eventArgs->result ?? $action->goBack();
    }

    public function failed(SocialController $action): mixed
    {
        $eventArgs = new ResultEventArgs($action);
        $this->trigger(self::EVENT_FAILED, $eventArgs);
        return $eventArgs->result ?? $action->goBack();
    }

    /**
     * @throws \Exception
     */
    public function error(SocialController $action, \Exception $ex): mixed
    {
        $eventArgs = new ErrorEventArgs($action, $ex);
        $this->trigger(self::EVENT_ERROR, $eventArgs);
        if($eventArgs->result === null) {
            throw $eventArgs->exception;
        }
        return $eventArgs->result;
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
            ->send($sender->toRequest($this->client), 'token')
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

    final public static function socialName(): string
    {
        $reflect = new \ReflectionClass(static::class);
        $attributes = $reflect->getAttributes(SocialName::class);
        $socialName = $reflect->getShortName();
        if(count($attributes) > 0) {
            $socialName = $attributes[0]->getArguments()[0];
        }
        return $socialName;
    }

    /**
     * Ссылка на oauth авторизацию
     * @param string $method
     * @param string|null $state
     * @return string
     */
    final public static function url(string $method, ?string $state = null) : string
    {
        return SocialConfiguration::url(static::socialName(), $method, $state);
    }
}