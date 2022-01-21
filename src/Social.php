<?php

namespace Celebron\social;

use Celebron\social\eventArgs\ErrorEventArgs;
use Celebron\social\eventArgs\SuccessEventArgs;
use Exception;
use ReflectionClass;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\httpclient\Client;
use yii\httpclient\CurlTransport;
use yii\httpclient\Request;
use yii\web\Controller;
use yii\web\IdentityInterface;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

/**
 *
 * @property-read Client $client
 */
abstract class Social extends Model
{
    public const EVENT_REGISTER_SUCCESS = "registerSuccess";
    public const EVENT_LOGIN_SUCCESS = 'loginSuccess';
    public const EVENT_ERROR = "error";

    public string $field;
    public string $clientUrl;

    public string $state;
    public ?string $code;
    public string $redirectUrl;


    protected mixed $id;
    protected array $data = [];

    private ?Client $_client = null;

    /**
     * @return array
     */
    public function rules (): array
    {
        return [
            [[ 'redirectUrl', 'clientUrl' ], 'url' ],
            ['field', 'fieldValidator' ],
            ['code', 'codeValidator', 'skipOnEmpty' => false ],
        ];
    }

    /**
     * @param $a
     * @param $p
     * @return void
     */
    final public function fieldValidator($a, $p)
    {
        //TODO: Реализация системы проверки филда
    }

    /**
     * @throws NotFoundHttpException
     */
    final public function codeValidator($a)
    {
        if ($this->$a === null) {
            $this->requestCode();
            return;
        }

        $this->id = $this->requestId();

        if ($this->id === null) {
            throw new NotFoundHttpException('User id not found to social ' . static::socialName());
        }
        static::debug("User id $this->id");
    }

    abstract protected function requestCode ();

    abstract protected function requestId () : mixed;


    /**
     * CurlClient
     * @return Client
     */
    final public function getClient (): Client
    {
        if ($this->_client === null) {
            $this->_client = new Client();
            $this->_client->transport = CurlTransport::class;
        }
        $this->_client->baseUrl = $this->clientUrl;
        return $this->_client;
    }

    /**
     * Выполниет редирет
     * @param $value
     */
    final public function redirect($value) : void
    {
        if($value instanceof Request) {
            $value = $value->fullUrl;
        }
        Yii::$app->response->redirect($value)->send();
    }

    /**
     * Поиск по полю в бд
     * @return ActiveRecord|null
     * @throws InvalidConfigException|NotSupportedException
     */
    protected function fieldSearch(): ?IdentityInterface
    {
        $class = Yii::createObject(Yii::$app->user->identityClass);
        if($class instanceof FieldSearchInterface){
            return $class::fieldSearch($this->field, $this->id);
        }
        throw new NotSupportedException($class::class . ' does not extend ' . FieldSearchInterface::class);
    }

    /**
     * Регистрация пользователя из социальной сети
     * @return bool
     */
    final public function register() : bool
    {
        /** @var ActiveRecord $user */
        $user = Yii::$app->user->identity;
        if($this->validate()) {
            $field = $this->field;
            $user->$field = $this->id;
            if(!$user->save()) {
                self::debug("Not registered user id $this->id.");
                $this->addError($field, $user->errors[$field]);
                return false;
            }

            self::debug("Registered user id $this->id");
            return true;
        }
        self::warning($this->errors);
        return false;
    }


    /**
     * Авторизация в системе
     * @param int $duration
     * @return bool
     * @throws InvalidConfigException
     * @throws NotSupportedException
     */
    final public function login(int $duration = 0) : bool
    {
        if($this->validate() && ( ($user = $this->fieldSearch()) !== null )) {
            $login = Yii::$app->user->login($user, $duration);
            self::debug("User login ($this->id) " . $login ? "succeeded": "failed");
            return $login;
        }
        self::warning($this->errors);
        return false;
    }

    /**
     * Событие положительной авторизации
     * @param Controller $controller
     * @return Response
     */
    public function loginSuccess(Controller $controller): mixed
    {
        $eventArgs = new SuccessEventArgs($controller);
        $this->trigger(self::EVENT_LOGIN_SUCCESS, $eventArgs);
        return $eventArgs->result;
    }

    /**
     * Событие положительной регистрации
     * @param Controller $controller
     * @return mixed
     */
    public function registerSuccess(Controller $controller): mixed
    {
        $eventArgs = new SuccessEventArgs($controller);
        $this->trigger(self::EVENT_REGISTER_SUCCESS, $eventArgs);
        return $eventArgs->result;
    }

    /**
     * Событие на ошибку
     * @param Controller $controller
     * @return mixed
     * @throws UnauthorizedHttpException
     */
    public function error(Controller $controller): mixed
    {
        $eventArgs = new ErrorEventArgs($controller);
        $eventArgs->errors = $this->errors;
        $eventArgs->result = new UnauthorizedHttpException("User $this->id not found.");
        $this->trigger(self::EVENT_ERROR, $eventArgs);
        if($eventArgs->result instanceof Exception) {
            throw $eventArgs->result;
        }
        return $eventArgs->result;
    }

    /**
     * @return string
     */
    final public static function socialName(): string
    {
        return (new ReflectionClass(static::class))->getShortName();
    }

    /**
     * Ссылка на страницу авторизации
     * @param string|null $name
     * @return string
     * @throws InvalidConfigException
     */
    final public static function url(?string $name = null) : string
    {
        $name = $name ?? static::socialName();
        return Url::to([ SocialConfiguration::config()->route, 'state' => $name ]);
    }

    /**
     * @throws InvalidConfigException
     */
    final public static function a(string $text, ?string $register=null, array $data = []): string
    {
        unset($data['class']);
        return Html::a($text, static::url($register), ArrayHelper::merge([
            'class' => 'social-' .strtolower(self::socialName()),
        ], $data));
    }

    protected static function debug($text): void
    {
        Yii::debug('[' . static::socialName() . ']' . $text, static::class);
    }

    protected static function warning($text): void
    {
        Yii::warning($text, static::class);
    }
}