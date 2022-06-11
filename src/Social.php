<?php

namespace Celebron\social;

use Celebron\social\eventArgs\ErrorEventArgs;
use Celebron\social\eventArgs\SuccessEventArgs;
use Exception;
use ReflectionClass;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\httpclient\Client;
use yii\httpclient\Request;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

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
    protected array $data = [];
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
     * @param $a
     * @param $p
     * @return void
     * @throws InvalidConfigException
     */
    final public function fieldValidator($a)
    {
        $class = Yii::createObject(Yii::$app->user->identityClass);
        if(($class instanceof ActiveRecord) && !ArrayHelper::isIn($this->$a, $class->attributes())) {
            $this->addError($a, "Field {$this->$a} not exists");
        }
    }

    /**
     * @param $a
     * @param $p
     */
    final public function codeValidator($a)
    {
        if ($this->$a === null) {
            $this->requestCode();
            return;
        }

        $this->_id = $this->requestId();

        if ($this->_id === null) {
            $this->addError($this->$a, "Request returned null");
        }
        static::debug("User id $this->_id");
    }

    abstract protected function requestCode ();

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
     * @return FieldSearchInterface|null
     * @throws InvalidConfigException
     * @throws NotSupportedException
     */
    protected function fieldSearch(): ?FieldSearchInterface
    {
        $class = Yii::createObject(Yii::$app->user->identityClass);
        if($class instanceof FieldSearchInterface){
            return $class::fieldSearch($this->field, $this->_id);
        }
        throw new NotSupportedException($class::class . ' does not extend ' . FieldSearchInterface::class);
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
            self::debug("Not registered user id $this->_id.");
            $this->addError($field, $user->errors[$field]);
        }
        return false;
    }

    /**
     * Авторизация в системе
     * @param int $duration
     * @return bool
     * @throws InvalidConfigException
     * @throws NotSupportedException
     */
    final public function login(int $duration = 0) : bool
    {
        if($this->active && $this->validate() && ( ($user = $this->fieldSearch()) !== null )) {
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
        $eventArgs->data = $this->data;
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
        $eventArgs->data = $this->data;
        $this->trigger(self::EVENT_REGISTER_SUCCESS, $eventArgs);
        return $eventArgs->result ?? $action->goBack();
    }

    /**
     * Событие на ошибку
     * @param SocialController $action
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws UnauthorizedHttpException
     */
    public function error(SocialController $action): mixed
    {
        $eventArgs = new ErrorEventArgs($action);
        $eventArgs->errors = $this->getErrorSummary(false);
        $eventArgs->data = $this->data;

        if($this->active) {
            if($this->hasErrors()) {
                $text = implode("\n",$eventArgs->errors);
                $eventArgs->result = new ForbiddenHttpException('Social ' . static::socialName() . " validation failed.\n {$text}");
            } else {
                $eventArgs->result = new UnauthorizedHttpException("User $this->_id not found.");
            }
        } else {
            $eventArgs->result = new ForbiddenHttpException( 'Social ' . static::socialName() . " not active.");
        }
        $this->trigger(self::EVENT_ERROR, $eventArgs);

        if($eventArgs->result instanceof Exception) { throw $eventArgs->result; }

        return $eventArgs->result;
    }

    /**
     * @return string
     */
    final public static function socialName(): string
    {
        return (new ReflectionClass(static::class))->getShortName();
    }

    /**
     * @throws InvalidConfigException
     */
    public static function urlState($state)
    {
        return Url::to([ SocialConfiguration::config()->route, 'state' => strtolower($state) ]);
    }

    /**
     * @param bool|string $state
     * @return string
     * @throws InvalidConfigException
     * @throws \yii\web\NotFoundHttpException
     */
    final public static function url(bool|string $state = false) : string
    {
        return SocialConfiguration::link(static::socialName(), $state);
    }

    /**
     * Ссылка на социальную сеть [html::a]
     * @param string $text - Текст на ссылку
     * @param bool|string $state
     * @param array $data
     * @return string
     * @throws InvalidConfigException
     * @throws \yii\web\NotFoundHttpException
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

    protected static function debug($text): void
    {
        Yii::debug('[' . static::socialName() . ']' . $text, static::class);
    }

}