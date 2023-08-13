<?php

namespace Celebron\socialSource;

use Celebron\common\Token;
use Celebron\socialSource\behaviors\ViewerBehavior;
use Celebron\socialSource\interfaces\OAuth2Interface;
use Celebron\socialSource\interfaces\ViewerInterface;
use Celebron\socialSource\data\CodeData;
use Celebron\socialSource\data\IdData;
use Celebron\socialSource\data\TokenData;
use Celebron\socialSource\responses\Code;
use Celebron\socialSource\responses\Id;
use yii\base\InvalidConfigException;
use yii\base\InvalidRouteException;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Session;

/**
 * @property string $clientSecret
 * @property string $clientId
 * @property string $redirectUrl - устанавливать если используется консоль (в остальных случаях автогенерация)
 * @property bool $active
 */
abstract class OAuth2 extends Social implements OAuth2Interface
{
    public const EVENT_DATA_CODE = 'dataCode';
    public const EVENT_DATA_TOKEN = 'dataToken';

    private ?string $_clientId = null;
    private ?string $_clientSecret = null;
    private ?string $_redirectUrl = null;

    public function behaviors ()
    {
        $behaviors = parent::behaviors();
        if($this instanceof ViewerInterface) {
            $behaviors[ViewerInterface::class] = new ViewerBehavior($this->socialName, $this->configure);
        }
        return $behaviors;
    }

    public function handleCode(State $state):Code
    {
        $request = new CodeData($this, $state);
        return $this->requestCode($request);
    }

    public function handleToken(string $code):Token
    {
        $request = new TokenData($code, $this);
        $token = $this->requestToken($request);
    }

    /**
     * @throws InvalidRouteException
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function request (?string $code, State $state, ...$args): ?Id
    {
        $session = \Yii::$app->get('session', false) ?? [];
        if ($session instanceof Session && !$session->isActive) {
            $session->open();
        }

        if ($code === null) {
            $session['social_random'] = $state->random;
            $this->handleCode($state)->redirect();
        }

        $equalRandom = true;
        if ($session instanceof Session) {
            $equalRandom = $state->equalRandom($session['social_random']);
            \Yii::$app->session->remove('social_random');
        }

        if ($equalRandom) {
            $token = $this->handleToken($code);
        } else {
            throw new BadRequestHttpException(\Yii::t('social','Random value does not match'));
        }

        $request = new IdData($this, $token);
        $response = $this->requestId($request);

        \Yii::debug("Userid: {$response->getId()}.", static::class);
        return $response;
    }


    public function url(string $action, ?string $state = null):string
    {
        return Url::toRoute([
            0 => $this->configure->route . '/handler',
            'social' => $this->socialName,
            'state' => (string)State::create($action, $state),
        ], true);
    }

    /**
     * @throws InvalidConfigException
     */
    public function getClientId():string
    {
        if(empty($this->_clientId)) {
            if(!empty($this->params['clientId'])) {
                return $this->params['clientId'];
            }
            throw new InvalidConfigException('Param "clientId" to social "' . $this->socialName . '" empty');
        }
        return $this->_clientId;
    }
    public function setClientId(string $value): void
    {
        $this->_clientId = $value;
    }

    /**
     * @throws InvalidConfigException
     */
    public function getClientSecret() : string
    {
        if(empty($this->_clientSecret)) {
            if(!empty($this->params['clientSecret'])) {
                return $this->params['clientSecret'];
            }
            throw new InvalidConfigException('Param "clientSecret" to social "' . $this->socialName. '" empty');
        }
        return $this->_clientSecret;
    }
    public function setClientSecret(string $value): void
    {
        $this->_clientSecret = $value;
    }

    public function getRedirectUrl()  : string
    {
        if(\Yii::$app instanceof \yii\web\Application) {
            return Url::toRoute([
                "{$this->configure->route}/handler",
                'social' => $this->socialName,
            ], true);
        }

        return $this->_redirectUrl ?? $this->defaultRedirectUrl();
    }
    public function setRedirectUrl(string $value): void
    {
        $this->_redirectUrl = $value;
    }
    public function defaultRedirectUrl():string
    {
        throw new InvalidConfigException('Param "redirectUrl" to social "' . $this->socialName . '" empty');
    }
}