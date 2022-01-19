<?php

namespace Celebron\social;

use Celebron\social\eventArgs\ErrorEventArgs;
use Celebron\social\eventArgs\SuccessEventArgs;
use yii\base\DynamicModel;
use yii\base\InvalidArgumentException;
use yii\base\Model;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\di\Instance;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\httpclient\Client;
use yii\httpclient\CurlTransport;
use yii\httpclient\Request;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\IdentityInterface;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

/**
 *
 * @property string $clientUrl
 * @property-read Client $client
 */
abstract class SocialBase extends Model
{
    public const EVENT_REGISTER_SUCCESS = "registerSuccess";
    public const EVENT_LOGIN_SUCCESS = 'loginSuccess';
    public const EVENT_ERROR = "error";

    public const MODE_REGISTER = "register";
    public const MODE_LOGIN = "login";

    /** @var string - поле в базе данных для сравнения */
    public string $field;
    public string $clientUrl = '';

    public mixed $id;
    public string $redirectUrl;

    protected mixed $data;
    private ?Client $_client = null;

    /**
     * @return string
     */
    public static function getSocialName(): string
    {
        $r = new \ReflectionClass(static::class);
        return strtolower($r->getShortName());
    }

    /**
     * @throws BadRequestHttpException
     */
    public function validateCode (?string $code, string $state): void
    {
        if ($code === null) {
            $this->requestCode($state);
            exit;
        }
        $this->id = $this->requestId($code);
        \Yii::debug("Client id = {$this->id}", static::class);
    }


    /**
     * СurlClient
     * @return Client
     */
    public function getClient (): Client
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
    public function redirect($value) : void
    {
        if($value instanceof Request) {
            $value = $value->fullUrl;
        }
        \Yii::$app->response->redirect($value)->send();
    }

    /**
     * Правила валидации
     * @return array[]
     */
    public function rules (): array
    {
        return [
            [['redirectUrl', 'id'], 'required'],
            ['redirectUrl','url']
        ];
    }

    /**
     * Поиск по полю в бд
     * @return ActiveRecord|null
     * @throws \yii\base\InvalidConfigException
     */
    protected function fieldSearch(): ?ActiveRecord
    {
        $class = Instance::ensure(\Yii::$app->user->identityClass,FieldSearchInterface::class);
        /** @var FieldSearchInterface $class */
        return $class::fieldSearch($this->field,$this->id);
    }


    /**
     * Метод получения Id;
     * @return mixed
     */
    abstract public function requestId (string $code): mixed;

    /**
     * метод запроса кода
     * @return mixed
     */
    abstract public function requestCode (string $state): void;

    /**
     * Регистрация пользователя из социальной сети
     * @return bool
     */
    final public function register() : bool
    {
        /** @var ActiveRecord $user */
        $user = \Yii::$app->user->identity;
        if($this->validate()) {
            $field = $this->field;
            $user->$field = $this->id;
            if(!$user->save()) {
                $this->addError($field, $user->errors[$field]);
                return false;
            }
            \Yii::debug(\Yii::t('app','Registration {id}',[
                'id' => $this->id,
            ]), static::class);
            return true;
        }
        return false;
    }



    /**
     * Авторизация в системе
     * @param bool $remember
     * @return bool
     * @throws NotSupportedException
     * @throws \yii\base\InvalidConfigException
     */
    final public function login(int $duration = 0) : bool
    {
        /** @var IdentityInterface $user */
        if($this->validate() && ( ($user = $this->FieldSearch()) !== null )) {
            $login = \Yii::$app->user->login($user, $duration);

            \Yii::debug(\Yii::t('app','Authorization {status}',[
                'status' => $login?"successful":"failed",
            ]), static::class);
            return $login;
        }

        \Yii::debug(\Yii::t('app','Authorization failed'), static::class);
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
        $eventArgs->result = new UnauthorizedHttpException("User {$this->id} not found.");
        $this->trigger(self::EVENT_ERROR, $eventArgs);
        if($eventArgs->result instanceof \Exception) {
            throw $eventArgs->result;
        }
        return $eventArgs->result;
    }

    /**
     * Ссылка на стараницу авторизации
     * @param $state
     * @param
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public static function urlState($state): string
    {
        return Url::to([SocialConfiguration::config()->route,'state'=>$state]);
    }

    /**
     * Ссылка на страницу авторизации
     * @param bool $register
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public static function url(bool $register = false) : string
    {
        $name = static::getSocialName();
        if($register) {
            $name .= "_" . self::MODE_REGISTER;
        }
        return static::urlState(strtolower($name));
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public static function a(string $text, $register=false): string
    {
        $reflection = new \ReflectionClass(static::class);
        return Html::a($text, static::url($register), [
            'class'=> 'social-' .strtolower($reflection->getShortName())
        ]);
    }


}