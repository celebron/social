<?php

namespace Celebron\social;

use Celebron\social\eventArgs\ErrorEventArgs;
use Celebron\social\eventArgs\FindUserEventArgs;
use Celebron\social\eventArgs\SuccessEventArgs;
use Exception;
use ReflectionClass;
use Yii;
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
 * @property-read mixed $id - (для чтения) Id из соцеальной сети (относительно field)
 * @property-read Client $client - (для чтения) Http Client
 */
abstract class Social extends Model
{
    public const EVENT_REGISTER_SUCCESS = "registerSuccess";
    public const EVENT_LOGIN_SUCCESS = 'loginSuccess';
    public const EVENT_ERROR = "error";
    public const EVENT_FIND_USER = "findUser";

    ////В config

    /** @var string - поле в базе данных для идентификации  */
    public string $field;
    /** @var bool - разрешить использование данной социальной сети  */
    public bool $active = false;

    ////Визуал

    /** @var string - название для виджета */
    public string $name;

    /** @var string - иконка для виджета */
    public string $icon;


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
    private mixed $_id = null;

    /**
     * Отображение Id из соц. сети
     * @return mixed
     */
    public function getId(): mixed
    {
        return $this->_id;
    }

    /**
     * Правила проверки данных
     * @return array
     */
    public function rules (): array
    {
        return [
            ['redirectUrl', 'url' ],
            ['field', 'fieldValidator'],
            ['code', 'codeValidator', 'skipOnEmpty' => false ],
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

        $this->_id = $this->requestId();
        static::debug("User id $this->_id");

        if ($this->_id === null) {
            throw new NotFoundHttpException("User not found", code: 2);
        }

    }

    /**
     * Запрос кода от соц.сети
     * @return mixed
     */
    abstract protected function requestCode ();

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
        $findUserEventArgs = new FindUserEventArgs($class::find());
        $this->trigger(self::EVENT_FIND_USER, $findUserEventArgs);
        \Yii::info($findUserEventArgs->user?->toArray(), static::class);
        return $findUserEventArgs->user;
    }

    /**
     * Регистрация пользователя из социальной сети
     * @return bool
     */
    final public function register() : bool
    {
        /** @var ActiveRecord $user */
        $user = Yii::$app->user->identity;
        if($this->active && $this->validate()) {
            $field = $this->field;
            $user->$field = $this->_id;
            if($user->save()) {
                self::debug("Registered user id $this->_id");
                return true;
            }
            \Yii::warning($user->errors, static::class);
        }
        return false;
    }

    /**
     * Авторизация в системе
     * @param int $duration
     * @return bool
     * @throws InvalidConfigException
     */
    final public function login(int $duration = 0) : bool
    {
        if($this->active && $this->validate() && ( ($user = $this->findUser()) !== null )) {
            $login = Yii::$app->user->login($user, $duration);
            self::debug("User login ($this->_id) " . $login ? "succeeded": "failed");
            return $login;
        }
        return false;
    }

    /**
     * Событие положительной авторизации
     * @param SocialController $action
     * @return Response
     */
    public function loginSuccess(SocialController $action): mixed
    {
        $eventArgs = new SuccessEventArgs($action);
        $this->trigger(self::EVENT_LOGIN_SUCCESS, $eventArgs);
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

        if(!$this->active) {
            throw new ForbiddenHttpException('Social ' . static::socialName() . " not active.");
        }

        if($eventArgs->result === null) {
            if($ex === null) {
                throw new NotFoundHttpException('['. static::socialName() .'] User ' . $this->_id .' not registered');
            }
            throw $ex;
        }

        return $eventArgs->result;
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
     * @param bool|string $state
     * @return string
     */
    final public static function url(bool|string $state = false) : string
    {
        return SocialConfiguration::link(static::socialName(), $state);
    }

    /**
     * Ссылка на социальную сеть [html::a]
     * @param string $text - Текст на ссылку
     * @param bool|string $state - oauth state (true - register)
     * @param array $data
     * @return string
     */
    final public static function a(string $text, bool|string $state = false, array $data = []): string
    {
        if(isset($data['class'])) {
            $data['class'] = [
                'social-' .strtolower(self::socialName()),
                $data['class']
            ];
        }
        return Html::a($text, static::url($state), $data);
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