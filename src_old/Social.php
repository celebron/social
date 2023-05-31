<?php

namespace Celebron\src_old;

use Celebron\social\old\eventArgs\RequestArgs;
use Celebron\social\old\interfaces\AuthActionInterface;
use Yii;
use yii\base\InvalidConfigException;
use yii\httpclient\Client;

/***
 * Стандартный класс авторизации в социальных сетях.
 * Реализация actionRegister, actionDelete, actionLogin
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
}