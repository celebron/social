<?php

namespace Celebron\social;

use Celebron\social\eventArgs\RequestArgs;
use Celebron\social\interfaces\AuthActionInterface;
use Yii;
use yii\base\InvalidConfigException;
use yii\httpclient\Client;

/**
 * Базовый класс авторизации соц.сетей.
 * @property-read null|array $stateDecode
 * @property-read mixed $socialId
 * @property-read Client $client - (для чтения) Http Client
 */
abstract class Social extends OAuth2 implements AuthActionInterface
{
    public const METHOD_REGISTER = 'register';
    public const METHOD_DELETE = 'delete';
    public const METHOD_LOGIN = 'login';

    /**
     * Регистрация пользователя из социальной сети
     * @param RequestArgs $args
     * @return bool
     * @throws InvalidConfigException
     */
    #[OAuth2Request]
    final public function actionRegister(RequestArgs $args) : bool
    {
        \Yii::debug("Register social '" . static::socialName() ."' to user");
        return $this->modifiedUser($this->id);
    }

    /**
     * Удаление записи соц УЗ.
     * @param RequestArgs $args
     * @return bool
     * @throws InvalidConfigException
     */
    final public function actionDelete(RequestArgs $args) : bool
    {
        \Yii::debug("Delete social '" . static::socialName() . "' to user");
        return $this->modifiedUser(null);
    }

    /**
     * Авторизация в системе
     * @param RequestArgs $args
     * @return bool
     * @throws InvalidConfigException
     */
    #[OAuth2Request]
    final public function actionLogin(RequestArgs $args) : bool
    {
        if(($user = $this->findUser($this->id)) !== null) {
            $login = Yii::$app->user->login($user, $args->config->duration);
            \Yii::debug("User login ({$this->id}) " . $login ? "succeeded": "failed", static::class);
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