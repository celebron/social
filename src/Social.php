<?php

namespace Celebron\social;

use Exception;
use Yii;
use yii\base\InvalidConfigException;

use yii\helpers\ArrayHelper;
use yii\httpclient\Client;
use yii\httpclient\Request;
use yii\web\BadRequestHttpException;

/**
 * Базовый класс авторизации соц.сетей.
 * @property-read null|array $stateDecode
 * @property-read mixed $socialId
 * @property-read Client $client - (для чтения) Http Client
 */
abstract class Social extends OAuth2
{


    public const METHOD_REGISTER = 'register';
    public const METHOD_DELETE = 'delete';
    public const METHOD_LOGIN = 'login';
    ////В config

    /** @var string - поле в базе данных для идентификации  */
    public string $field;

    /** @var mixed|null - Id от соцеальных сетей */
    public mixed $id = null;


    /**
     * @throws InvalidConfigException
     */
    public function getSocialId(): mixed
    {
        $this->fieldValidator();
        return \Yii::$app->user->identity->{$this->field};
    }


    /**
     * Регистрация пользователя из социальной сети
     * @return bool
     * @throws InvalidConfigException
     */
    #[OAuth2Request]
    final public function actionRegister() : bool
    {
        \Yii::debug("Register social '" . static::socialName() ."' to user");
        return $this->modifiedUser($this->id);
    }

    /**
     * Удаление записи соц УЗ.
     * @return bool
     * @throws InvalidConfigException
     */
    final public function actionDelete() : bool
    {
        \Yii::debug("Delete social '" . static::socialName() . "' to user");
        return $this->modifiedUser(null);
    }

    /**
     * Авторизация в системе
     * @param SocialConfiguration $config
     * @return bool
     * @throws InvalidConfigException
     */
    #[OAuth2Request]
    final public function actionLogin(SocialConfiguration $config) : bool
    {
        if(($user = $this->$this->findUser($this->id)) !== null) {
            $login = Yii::$app->user->login($user, $config->duration);
            \Yii::debug("User login ($this->id) " . $login ? "succeeded": "failed", static::class);
            return $login;
        }
        return false;
    }

    /**
     * @param Request|RequestToken $sender
     * @param string|\Closure|array $field
     * @return mixed
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws \yii\httpclient\Exception
     * @throws Exception
     */
    protected function sendToField(Request|RequestToken $sender, string|\Closure|array $field) : mixed
    {
        if($sender instanceof  RequestToken) {
            $sender->send = false;
            $sender = $sender->sender();
        }
        $response = $this->send($sender);
        return ArrayHelper::getValue($response->getData(), $field);
    }

    public static function urlLogin(?string $state = null): string
    {
        return static::url(self::METHOD_LOGIN, $state);
    }

    public static function urlRegister(?string $state= null): string
    {
        return static::url(self::METHOD_REGISTER, $state);
    }

    public static function urlDelete(?string $state= null): string
    {
        return static::url(self::METHOD_DELETE, $state);
    }
}