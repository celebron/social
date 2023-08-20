<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social;

use Celebron\common\Token;
use Celebron\source\social\traits\ViewerBehavior;
use Celebron\source\social\interfaces\OAuth2Interface;
use Celebron\source\social\interfaces\ViewerInterface;
use Celebron\source\social\data\CodeData;
use Celebron\source\social\data\IdData;
use Celebron\source\social\data\TokenData;
use Celebron\source\social\responses\Code;
use Celebron\source\social\responses\Id;
use yii\base\InvalidConfigException;
use yii\base\InvalidRouteException;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Session;

/**
 * @property string $clientSecret
 * @property string $clientId
 * @property string $redirectUrl
 * @property bool $active
 */
abstract class OAuth2 extends Social implements OAuth2Interface
{
    public const EVENT_DATA_CODE = 'dataCode';
    public const EVENT_DATA_TOKEN = 'dataToken';

    protected ?string $_clientId = null;
    protected ?string $_clientSecret = null;
    protected ?string $_redirectUrl = null;



    public function handleCode(State $state):Code
    {
        $request = new CodeData($this, $state);
        return $this->requestCode($request);
    }

    public function handleToken(string $code):Token
    {
        $request = new TokenData($code, $this);
        return $this->requestToken($request);
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
            'social' => $this->name,
            'state' => (string)State::create($action, $state),
        ], true);
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

    public function defaultRedirectUrl():string
    {
        throw new InvalidConfigException('Param "redirectUrl" to social "' . $this->socialName . '" empty');
    }

    public function getClientId (): string
    {
        return $this->_clientId;
    }

    public function getClientSecret (): string
    {
        return $this->_clientSecret;
    }

    public function setClientId(string $value):void
    {
        throw new InvalidConfigException('Write ' . get_class($this) . '::clientId configuration only');
    }
    public function setClientSecret(string $value):void
    {
        throw new InvalidConfigException('Write ' . get_class($this) . '::clientSecret configuration only');
    }
}