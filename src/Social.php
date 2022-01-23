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
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\httpclient\Client;
use yii\httpclient\Request;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\IdentityInterface;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

/**
 *Базовый класс авторизации соц.сетей.
 * @property-read mixed $id
 * @property-read Client $client
 */
abstract class Social extends Model
{
    public const EVENT_REGISTER_SUCCESS = "registerSuccess";
    public const EVENT_LOGIN_SUCCESS = 'loginSuccess';
    public const EVENT_ERROR = "error";

    ////В config

    /** @var string  */
    public string $field;
    public bool $active = false;


    ///В Controllers
    public string $state;
    public ?string $code;
    public string $redirectUrl;



    protected array $data = [];
    private mixed $_id = null;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @return array
     */
    public function rules (): array
    {
        return [
            [['redirectUrl'], 'url' ],
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
        if($class instanceof ActiveRecord) {
            $columns = $class->attributes();
            if(!ArrayHelper::isIn($this->$a, $columns)) {
                $this->addError($a, "Field {$this->$a} not exists");
            }
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
     * @return ActiveRecord|null
     * @throws InvalidConfigException|NotSupportedException
     */
    protected function fieldSearch(): ?IdentityInterface
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
            if(!$user->save()) {
                self::debug("Not registered user id $this->_id.");
                $this->addError($field, $user->errors[$field]);
                return false;
            }

            self::debug("Registered user id $this->_id");
            return true;
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
     * @param Controller $controller
     * @return Response
     */
    public function loginSuccess(Controller $controller): mixed
    {
        $eventArgs = new SuccessEventArgs($controller);
        $this->trigger(self::EVENT_LOGIN_SUCCESS, $eventArgs);
        if($eventArgs->result === null) {
            $controller->goBack();
            exit;
        }
        return $eventArgs->result;
    }

    /**
     * Событие положительной регистрации
     * @param Controller $controller
     * @return mixed
     */
    public function registerSuccess(Controller $controller): mixed
    {
        $eventArgs = new SuccessEventArgs($controller);
        $this->trigger(self::EVENT_REGISTER_SUCCESS, $eventArgs);
        if($eventArgs->result === null) {
            $controller->goBack();
            exit;
        }
        return $eventArgs->result;
    }

    /**
     * Событие на ошибку
     * @param Controller $controller
     * @return mixed
     * @throws UnauthorizedHttpException
     * @throws ForbiddenHttpException
     */
    public function error(Controller $controller): mixed
    {
        $eventArgs = new ErrorEventArgs($controller);
        $eventArgs->errors = $this->getErrorSummary(true);
        if($this->active) {
            $eventArgs->result = new UnauthorizedHttpException("User $this->_id not found.");
        } else {
            $eventArgs->result = new ForbiddenHttpException( 'Social ' . static::socialName() . " not active.");
        }
        $this->trigger(self::EVENT_ERROR, $eventArgs);
        if($eventArgs->result instanceof Exception) {
            throw $eventArgs->result;
        }
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
     * Ссылка на страницу авторизации
     * @param string|null $name
     * @return string
     * @throws InvalidConfigException
     */
    final public static function url(?string $tag = null) : string
    {
        $params[] = static::socialName();
        if($tag !== null) {
            $params[] = $tag;
        }
        return static::urlState(implode('_', $params));
    }

    /**
     * @throws InvalidConfigException
     */
    final public static function a(string $text, ?string $register=null, array $data = []): string
    {
        if(isset($data['class'])) {
            $data['class'] = [
                'social-' .strtolower(self::socialName()),
                $data['class']
            ];
        }
        return Html::a($text, static::url($register), $data);
    }

    protected static function debug($text): void
    {
        Yii::debug('[' . static::socialName() . ']' . $text, static::class);
    }

}