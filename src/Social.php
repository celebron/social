<?php

namespace Celebron\social;

use Celebron\social\interfaces\RequestIdInterface;
use Celebron\social\eventArgs\FindUserEventArgs;
use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\di\Instance;
use yii\di\NotInstantiableException;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;
use yii\httpclient\Request;
use yii\web\BadRequestHttpException;
use yii\web\IdentityInterface;
use yii\web\NotFoundHttpException;

/**
 * Базовый класс авторизации соц.сетей.
 * @property-read null|array $stateDecode
 * @property-read mixed $socialId
 * @property-read Client $client - (для чтения) Http Client
 */
abstract class Social extends OAuth2
{
    public const EVENT_FIND_USER = "findUser";

    public const METHOD_REGISTER = 'register';
    public const METHOD_DELETE = 'delete';
    public const METHOD_LOGIN = 'login';
    ////В config

    /** @var string - поле в базе данных для идентификации  */
    public string $field;



    /** @var mixed|null - Id от соцеальных сетей */
    public mixed $id = null;

    /**
     * Валидация поля аврторизации
     * @return void
     * @throws InvalidConfigException
     */
    final public function fieldValidator() : void
    {
        $class = Yii::createObject(Yii::$app->user->identityClass);
        if(!($class instanceof ActiveRecord)) {
            throw new NotInstantiableException(ActiveRecord::class, code: 0);
        }
        if(!ArrayHelper::isIn($this->field, $class->attributes())) {
            throw new InvalidConfigException('Field ' . $this->field . ' not supported to class ' .$class::class, code: 1);
        }
    }

    protected function requestSocialId() : void
    {
        if($this instanceof RequestIdInterface) {
            $requestId = new RequestId($this);
            $requestId->uri = $this->getUriInfo();
            $this->id = $this->requestId($requestId);
        }
        \Yii::debug("User id: {$this->id}", static::class);

        if ($this->id === null) {
            throw new NotFoundHttpException("User not found", code: 2);
        }
    }

    /**
     * Поиск по полю в бд
     * @return IdentityInterface|ActiveRecord
     * @throws InvalidConfigException
     */
    protected function findUser(): ?IdentityInterface
    {
        $this->fieldValidator();
        $class = Instance::ensure(\Yii::$app->user->identityClass, ActiveRecord::class);
        $query = $class::find()->andWhere([$this->field => $this->id]);
        $findUserEventArgs = new FindUserEventArgs($query);
        $this->trigger(self::EVENT_FIND_USER, $findUserEventArgs);
        \Yii::debug($findUserEventArgs->user?->toArray(), static::class);
        return $findUserEventArgs->user;
    }


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
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
     */
    #[\Celebron\social\Request]
    final public function register() : bool
    {
        $this->requestSocialId();
        \Yii::debug("Register social '" . static::socialName() ."' to user");
        return $this->modifiedUser($this->id);
    }

    /**
     * Удаление записи соц УЗ.
     * @return bool
     * @throws InvalidConfigException
     */
    final public function delete() : bool
    {
        \Yii::debug("Delete social '" . static::socialName() . "' to user");
        return $this->modifiedUser(null);
    }

    /**
     * Авторизация в системе
     * @param int $duration
     * @return bool
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    #[\Celebron\social\Request]
    final public function login(int $duration = 0) : bool
    {
        $this->requestSocialId();
        if(($user = $this->findUser()) !== null) {
            $login = Yii::$app->user->login($user, $duration);
            \Yii::debug("User login ($this->id) " . $login ? "succeeded": "failed", static::class);
            return $login;
        }
        return false;
    }


    /**
     * Модификация данных пользователя
     * @param mixed $data - Значение поля field в пользовательской модели
     * @return bool
     * @throws InvalidConfigException
     */
    protected function modifiedUser(mixed $data) : bool
    {
        $this->fieldValidator();
        /** @var ActiveRecord|IdentityInterface $user */
        $user = Yii::$app->user->identity;
        $field = $this->field;
        $user->$field = $data;

        if ($user->save()) {
            \Yii::debug("Save field ['{$field}' = {$data}] to user {$user->getId()}", static::class);
            return true;
        }
        \Yii::warning($user->getErrorSummary(true), static::class);
        return false;
    }

    /**
     * Выполнение отправки и получение Id
     * @throws \yii\httpclient\Exception
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     * @throws Exception
     */
    protected function sendReturnId(Request|RequestToken $sender, string|\Closure|array $field) : mixed
    {
        if($sender instanceof  RequestToken) {
            $sender = $sender->toRequest($this->client);
        }
        $response = $this->send($sender, 'info');
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