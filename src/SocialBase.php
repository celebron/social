<?php

namespace Celebron\social;

use yii\base\InvalidArgumentException;
use yii\base\Model;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\di\Instance;
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

    /** @var string - поле в базе данных для сравнения */
    public string $field;

    public string $state;

    protected mixed $data;

    public mixed $id;

    public string $redirectUrl;

    public string $clientUrl;

    private ?Client $_client = null;

    /**
     * @throws BadRequestHttpException
     */
    public function validateCode (?string $code): void
    {
        if ($code === null) {
            $this->requestCode();
            exit;
        }

        $this->id = $this->requestId($code);

        if($this->id === null) {
            throw new BadRequestHttpException("The request did not return a result.");
        }
        \Yii::debug("Client id = {$this->id}", static::class);
    }


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

    public function rules (): array
    {
        return [
            [['redirectUrl', 'id'], 'required'],
        ];
    }

    public function init ()
    {
        if($this->field === null) {
            throw new InvalidArgumentException("Property Field not set",1);
        }
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
    abstract public function requestCode (): void;


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
     * Событие положительной регистрации
     * @param Controller $controller
     * @return Response
     */
    public function registerSuccess(Controller $controller): Response
    {
        return $controller->goHome();
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
            $login = \Yii::$app->user->login($user,$duration);
            \Yii::debug(\Yii::t('app','Authorization {status}',[
                'status' => $login?"successful":"failed",
            ]), static::class);
            return $login;
        }

        \Yii::debug(\Yii::t('social','Authorization failed'), static::class);
        return false;
    }

    /**
     * Событие положительной авторизации
     * @param Controller $controller
     * @return Response
     */
    public function loginSuccess(Controller $controller): Response
    {
        return $controller->goBack();
    }

    /**
     * Событие на ошибку
     * @param Controller $controller
     * @throws HttpException
     */
    public function error(Controller $controller)
    {
        return throw new UnauthorizedHttpException("User {$this->id} not found.");
    }


    /**
     * Ссылка на стараницу авторизации
     * @param $state
     * @param
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public static function UrlState($state): string
    {
        return Url::to([self::config()->route,'state'=>$state]);
    }

    /**
     * Ссылка на страницу авторизации
     * @param bool $register
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public static function Url(bool $register = false) : string
    {
        $reflection = new \ReflectionClass(static::class);
        $name = $reflection->getShortName();
        if($register) {
            $name .= "_" . SocialAction::ACTION_REGISTER;
        }
        return static::UrlState(strtolower($name));
    }

    /**
     * @return SocialConfiguration
     * @throws \yii\base\InvalidConfigException
     */
    public static function config() : SocialConfiguration
    {
        return \Yii::$app->get(SocialConfiguration::class);
    }
}