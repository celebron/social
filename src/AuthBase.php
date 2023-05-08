<?php

namespace Celebron\social;

use Celebron\social\eventArgs\ErrorEventArgs;
use Celebron\social\eventArgs\FindUserEventArgs;
use Celebron\social\eventArgs\RequestArgs;
use Celebron\social\eventArgs\ResultEventArgs;
use Celebron\social\interfaces\AuthRequestInterface;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\di\Instance;
use yii\di\NotInstantiableException;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;

/**
 *
 * @property-read mixed $socialId
 */
abstract class AuthBase extends Model
{
    public const EVENT_ERROR = "error";
    public const EVENT_SUCCESS = 'success';
    public const EVENT_FAILED = 'failed';
    public const EVENT_FIND_USER = "findUser";
    public bool $active = true;
    public string $field;

    public function __construct ($config = [])
    {
        parent::__construct($config);
        $name = static::socialName();
        //Генерация констант под каждую соц.сеть
        $contName = 'SOCIAL_' . strtoupper($name);
        if(!defined($contName)) {
            define($contName, strtolower($name));
        }
    }

    /**
     * @throws \Exception
     */
    public function run(SocialController $controller): mixed
    {
        $requestArgs = new RequestArgs(
            $controller->config,
            $controller->getCode(),
            $controller->getState()
        );

        try {
            $methodRef = new \ReflectionMethod($this, $requestArgs->actionMethod);
            $requestArgs->requested = false;

            //Выполнить запрос во внешию систему
            if($this instanceof AuthRequestInterface) {
                \Yii::debug('Released interface ' . AuthRequestInterface::class, static::class);
                $this->request($methodRef, $requestArgs);
                $requestArgs->requested = true;
            }

            if($methodRef->invoke($this, $requestArgs)) {
                \Yii::debug("Method '{$requestArgs->actionMethod}' successful!", static::class);
                return $this->success($controller, $requestArgs);
            }
            \Yii::debug("Method '{$requestArgs->actionMethod}' failed!", static::class);
            return $this->failed($controller, $requestArgs);
        } catch (\Exception $ex) {
            \Yii::error($ex->getMessage(), static::class);
            return $this->error($controller, $ex, $requestArgs);
        }
    }

    protected function success(SocialController $action, RequestArgs $args): mixed
    {
        $eventArgs = new ResultEventArgs($action, $args);
        $this->trigger(self::EVENT_SUCCESS, $eventArgs);
        return $eventArgs->result ?? $action->goBack();
    }

    protected function failed(SocialController $action, RequestArgs $args): mixed
    {
        $eventArgs = new ResultEventArgs($action, $args);
        $this->trigger(self::EVENT_FAILED, $eventArgs);
        return $eventArgs->result ?? $action->goBack();
    }

    /**
     * @throws \Exception
     */
    protected function error(SocialController $action, \Exception $ex, RequestArgs $args): mixed
    {
        $eventArgs = new ErrorEventArgs($action, $args, $ex);
        $this->trigger(self::EVENT_ERROR, $eventArgs);
        if($eventArgs->result === null) {
            throw $eventArgs->exception;
        }
        return $eventArgs->result;
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
     * @throws InvalidConfigException
     */
    public function getSocialId(): mixed
    {
        $this->fieldValidator();
        return \Yii::$app->user->identity->{$this->field};
    }

    /**
     * Валидация поля аврторизации
     * @return void
     * @throws InvalidConfigException
     */
    final public function fieldValidator() : void
    {
        $class = \Yii::createObject(\Yii::$app->user->identityClass);
        if(!($class instanceof ActiveRecord)) {
            throw new NotInstantiableException(ActiveRecord::class, code: 0);
        }
        if(!ArrayHelper::isIn($this->field, $class->attributes())) {
            throw new InvalidConfigException('Field ' . $this->field . ' not supported to class ' . $class::class, code: 1);
        }
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
        $user = \Yii::$app->user->identity;
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
     * Поиск по полю в бд
     * @return IdentityInterface|ActiveRecord
     * @throws InvalidConfigException
     */
    protected function findUser(mixed $id): ?IdentityInterface
    {
        $this->fieldValidator();
        $class = Instance::ensure(\Yii::$app->user->identityClass, ActiveRecord::class);
        $query = $class::find()->andWhere([$this->field => $id]);
        $findUserEventArgs = new FindUserEventArgs($query);
        $this->trigger(self::EVENT_FIND_USER, $findUserEventArgs);
        \Yii::debug($findUserEventArgs->user?->toArray(), static::class);
        return $findUserEventArgs->user;
    }
}