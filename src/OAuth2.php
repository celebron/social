<?php

namespace Celebron\social;

use Celebron\social\eventArgs\ErrorEventArgs;
use Celebron\social\eventArgs\SuccessEventArgs;
use Celebron\social\interfaces\GetUrlsInterface;
use Celebron\social\interfaces\RequestIdInterface;
use Celebron\social\interfaces\SetFullUrlInterface;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\base\NotSupportedException;
use yii\httpclient\Client;
use yii\httpclient\CurlTransport;
use yii\httpclient\Exception;
use yii\httpclient\Request;
use yii\httpclient\Response;
use yii\web\BadRequestHttpException;


abstract class OAuth2 extends Model
{
    public const EVENT_ERROR = "error";

    public string $clientId;
    public string $clientSecret;

    public ?string $state;
    public ?string $code;
    public string $redirectUrl;
    protected readonly Client $client;


    protected array $data = [];
    protected ?Token $token = null;

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
    final public function request(): void
    {
        $session = \Yii::$app->session;
        if(!$session->isActive) { $session->open(); }

        if($this->code === null) {
            $request = new RequestCode($this);
            $this->requestCode($request);
            $session['social_random'] = $request->state['random'];

            $url = $this->client->get($request->generateUri());
            if($this instanceof SetFullUrlInterface) {
                $url->setFullUrl($this->setFullUrl($url));
            }
            //Перейти на соответвующую страницу
            \Yii::$app->response->redirect($url->getFullUrl(), checkAjax: false)->send();
            exit(0);
        }

        if($this->stateValidator()) {
            $request = new RequestToken($this);
            $this->requestToken($request);
            $this->token = $this->sendToken($request, 'token');
        }
    }

    /**
     * @throws \yii\base\Exception
     */
    protected function stateValidator(): bool
    {
        $session = \Yii::$app->session;
        $data = RequestCode::stateDecode($this->state);
        $result = $session['social_random'] === $data['random'];
        \Yii::$app->session->remove('social_random');
        return $result;
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

    /**
     * @throws \yii\base\Exception
     * @throws \ReflectionException
     */
    public function run(SocialController $controller)
    {
       $data = RequestCode::stateDecode($this->state);
       if($data['m'] !== null) {
           try {
               $methodName = strtolower(trim($data['m']));
               $methodRef = new \ReflectionMethod($this, $methodName);
               $attributes = $methodRef->getAttributes(\Celebron\social\Request::class);
               if(isset($attributes[0])) {
                    $this->request();
               }
               if($methodRef->invoke($this, $controller->config)) {
                    return $this->success($methodName . 'Success', $controller);
               }
               return $this->error($controller, null);
           } catch (\Exception $ex) {
               return $this->error($controller, $ex);
           }
       }
    }

    protected function success($method, SocialController $action): mixed
    {
        $eventArgs = new SuccessEventArgs($action);
        $this->trigger($method, $eventArgs);
        return $eventArgs->result ?? $action->goBack();
    }

    /**
     * @throws Exception
     */
    protected function error(SocialController $action, ?Exception $ex): mixed
    {
        $eventArgs = new ErrorEventArgs($action, $ex);
        $this->trigger(self::EVENT_ERROR, $eventArgs);

        if($eventArgs->result === null) {
            throw $ex;
        }

        return $eventArgs->result;
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    protected function send(Request $sender, string $theme = 'info') : Response
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
    protected function sendToken(RequestToken $sender) : Token
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
        $reflect = new ReflectionClass(static::class);
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
     * @throws \yii\base\Exception
     */
    final public static function url(string $method, ?string $state = null) : string
    {
        return SocialConfiguration::url(static::socialName(), $method, $state);
    }
}