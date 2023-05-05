<?php

namespace Celebron\social;

use Celebron\social\interfaces\AuthActionInterface;
use Yii;
use yii\base\InvalidConfigException;
use yii\httpclient\Client;

/**
 * Базовый класс авторизации соц.сетей.
 * @property-read null|array $stateDecode
 * @property-read mixed $socialId
 * @property mixed $id
 * @property-read Client $client - (для чтения) Http Client
 */
abstract class Social extends OAuth2 implements AuthActionInterface
{


    public const METHOD_REGISTER = 'register';
    public const METHOD_DELETE = 'delete';
    public const METHOD_LOGIN = 'login';

    private mixed $_id;

    public function getId():mixed
    {
        return $this->_id;
    }

    public function setId(mixed $id):void
    {
        $this->_id = $id;
    }

    /**
     * Регистрация пользователя из социальной сети
     * @param SocialConfiguration $config
     * @return bool
     * @throws InvalidConfigException
     */
    #[OAuth2Request]
    final public function actionRegister(SocialConfiguration $config) : bool
    {
        \Yii::debug("Register social '" . static::socialName() ."' to user");
        return $this->modifiedUser($this->id);
    }

    /**
     * Удаление записи соц УЗ.
     * @param SocialConfiguration $config
     * @return bool
     * @throws InvalidConfigException
     */
    final public function actionDelete(SocialConfiguration $config) : bool
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
        if(($user = $this->findUser($this->id)) !== null) {
            $login = Yii::$app->user->login($user, $config->duration);
            \Yii::debug("User login ($this->id) " . $login ? "succeeded": "failed", static::class);
            return $login;
        }
        return false;
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