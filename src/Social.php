<?php

namespace Celebron\social;

use Celebron\social\eventArgs\ErrorEventArgs;
use Celebron\social\eventArgs\FindUserEventArgs;
use Celebron\social\eventArgs\SuccessEventArgs;
use Exception;
use ReflectionClass;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\di\Instance;
use yii\di\NotInstantiableException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\httpclient\Client;
use yii\httpclient\Request;
use yii\web\ForbiddenHttpException;
use yii\web\IdentityInterface;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Базовый класс авторизации соц.сетей.
 * @property-read Client $client - (для чтения) Http Client
 */
abstract class Social extends Model
{
    public const EVENT_REGISTER_SUCCESS = "registerSuccess";
    public const EVENT_LOGIN_SUCCESS = 'loginSuccess';
    public const EVENT_ERROR = "error";
    public const EVENT_DELETE_SUCCESS = 'deleteSuccess';
    public const EVENT_FIND_USER = "findUser";

    public const SCENARIO_REQUEST = 'request';
    public const SCENARIO_RESPONSE = 'response';

    ////В config

    /** @var string - поле в базе данных для идентификации  */
    public string $field;
    /** @var bool - разрешить использование данной социальной сети  */
    public bool $active = false;
    /** @var bool - использование сессии для сохранения data */
    public bool $useSession = false;

    ////Визуал

    /** @var string - название для виджета */
    public string $name;

    /** @var string - иконка для виджета */
    public string $icon = '';


    ///В Controllers

    /** @var null|string - oAuth2 state */
    public ?string $state;
    /** @var string|null - oAuth2 code */
    public ?string $code;
    /** @var string - oAuth redirectUrl */
    public string $redirectUrl;

    /** @var array - Данные от социальных сетей */
    public array $data = [];
    /** @var mixed|null - Id от соцеальных сетей */
    public mixed $id = null;

    public function init ()
    {
        $name = static::socialName();
        //Генерация констант под каждую соц.сеть
        $contName = 'SOCIAL_' . strtoupper($name);
        if(!defined($contName)) {
            define($contName, strtolower($name));
        }
    }

    /**
     * Правила проверки данных
     * @return array
     */
    public function rules (): array
    {
        return [
            ['redirectUrl', 'url', 'on' => self::SCENARIO_REQUEST ],
            ['field', 'fieldValidator','on' => [self::SCENARIO_RESPONSE, self::SCENARIO_REQUEST]],
            ['code', 'codeValidator', 'skipOnEmpty' => false, 'on' => self::SCENARIO_REQUEST ],
        ];
    }

    /**
     * Валидация поля аврторизации
     * @param $a
     * @return void
     * @throws InvalidConfigException
     */
    final public function fieldValidator($a) : void
    {
        $class = Yii::createObject(Yii::$app->user->identityClass);
        if(!($class instanceof ActiveRecord)) {
            throw new NotInstantiableException(ActiveRecord::class, code: 0);
        }
        if(!ArrayHelper::isIn($this->$a, $class->attributes())) {
            throw new InvalidConfigException('Field ' . $this->$a . ' not supported to class ' .$class::class, code: 1);
        }
    }

    /**
     * Валидация кода
     * @param $a
     * @throws NotFoundHttpException
     */
    final public function codeValidator($a): void
    {
        if ($this->$a === null) {
            $this->requestCode();
            return;
        }

        $this->id = $this->requestId();
        static::debug("User id $this->id");

        if ($this->id === null) {
            throw new NotFoundHttpException("User not found", code: 2);
        }
    }

    /**
     * Запрос кода от соц.сети
     * @return void;
     */
    abstract protected function requestCode () : void;

    /**
     * Запрос id пользователя от соц.сети
     * @return mixed
     */
    abstract protected function requestId () : mixed;

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
     * @return IdentityInterface|ActiveRecord
     * @throws InvalidConfigException
     */
    protected function findUser(): ?IdentityInterface
    {
        $class = Instance::ensure(\Yii::$app->user->identityClass, ActiveRecord::class);
        $query = $class::find()->andWhere([$this->field => $this->id]);
        $findUserEventArgs = new FindUserEventArgs($query);
        $this->trigger(self::EVENT_FIND_USER, $findUserEventArgs);
        \Yii::debug($findUserEventArgs->user?->toArray(), static::class);
        return $findUserEventArgs->user;
    }

    /**
     * @return mixed
     * @throws NotSupportedException
     */
    public function getSocialId(): mixed
    {
        $this->scenario = self::SCENARIO_RESPONSE;
        if($this->validate()) {
            return \Yii::$app->user->identity->{$this->field};
        }
        throw new NotSupportedException('Not validate Social class');
    }

    /**
     * Регистрация пользователя из социальной сети
     * @return bool
     */
    final public function register() : bool
    {
        $this->scenario =  self::SCENARIO_REQUEST;
        return $this->validate() && $this->modifiedUser($this->id);
    }

    /**
     * Удаление записи соц УЗ.
     * @return bool
     */
    final public function delete() : bool
    {
        $this->scenario = self::SCENARIO_RESPONSE;
        return $this->validate() && $this->modifiedUser(null);
    }

    /**
     * Авторизация в системе
     * @param int $duration
     * @return bool
     * @throws InvalidConfigException
     */
    final public function login(int $duration = 0) : bool
    {
        $this->scenario = self::SCENARIO_REQUEST;
        if($this->validate() && ( ($user = $this->findUser()) !== null )) {
            $login = Yii::$app->user->login($user, $duration);
            self::debug("User login ($this->id) " . $login ? "succeeded": "failed");
            return $login;
        }
        return false;
    }

    public function deleteSuccess(SocialController $action)
    {
        $eventArgs = new SuccessEventArgs($action);
        $eventArgs->useSession = $this->useSession;
        $this->trigger(self::EVENT_DELETE_SUCCESS, $eventArgs);
        if($eventArgs->useSession) {
            if(!\Yii::$app->session->isActive) {
                \Yii::$app->session->open();
            }
            $session = \Yii::$app->session;
            \Yii::debug('Used session to save token', static::class);
            $session[static::socialName() . '.token'] = null;
        }
        return $eventArgs->result ?? $action->goBack();
    }

    /**
     * Событие положительной авторизации
     * @param SocialController $action
     * @return Response
     */
    public function loginSuccess(SocialController $action): mixed
    {
        $eventArgs = new SuccessEventArgs($action);
        $eventArgs->useSession = $this->useSession;
        $this->trigger(self::EVENT_LOGIN_SUCCESS, $eventArgs);
        if($eventArgs->useSession) {
            if(!\Yii::$app->session->isActive) {
                \Yii::$app->session->open();
            }
            $session = \Yii::$app->session;
            \Yii::debug('Used session to save token', static::class);
            $session[static::socialName() . '.token'] = $this->data['token'];
        }
        return $eventArgs->result ?? $action->goBack();
    }

    /**
     * Событие положительной регистрации
     * @param SocialController $action
     * @return mixed
     */
    public function registerSuccess(SocialController $action): mixed
    {
        $eventArgs = new SuccessEventArgs($action);
        $this->trigger(self::EVENT_REGISTER_SUCCESS, $eventArgs);
        return $eventArgs->result ?? $action->goBack();
    }

    /**
     * Событие на ошибку
     * @param SocialController $action
     * @param Exception|null $ex
     * @return mixed
     * @throws ForbiddenHttpException|NotFoundHttpException
     * @throws Exception
     */
    public function error(SocialController $action, ?Exception $ex): mixed
    {
        $eventArgs = new ErrorEventArgs($action, $ex);
        $this->trigger(self::EVENT_ERROR, $eventArgs);

        if($eventArgs->result === null) {
            throw $ex;
        }

        return $eventArgs->result;
    }

    /**
     * Модификация данных пользователя
     * @param mixed $data - Значение поля field в пользовательской модели
     * @return bool
     */
    protected function modifiedUser(mixed $data) : bool
    {
        /** @var ActiveRecord|IdentityInterface $user */
        $user = Yii::$app->user->identity;
        $field = $this->field;
        $user->$field = $data;

        if ($user->save()) {
            self::debug("Save field ['{$field}' = {$data}] to user {$user->getId()}");
            return true;
        }
        \Yii::warning($user->getErrorSummary(true), static::class);
        return false;
    }


    /**
     * Название класса
     * @return string
     */
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
     * @param bool|string|null $state
     * @return string
     */
    final public static function url(bool|string|null $state = false) : string
    {
        return SocialConfiguration::url(static::socialName(), $state);
    }

    /**
     * Дебаг
     * @param $text
     * @return void
     */
    protected static function debug($text): void
    {
        Yii::debug('[' . static::socialName() . ']' . $text, static::class);
    }

}