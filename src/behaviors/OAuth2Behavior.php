<?php

namespace Celebron\socialSource\behaviors;

use yii\base\InvalidConfigException;
use yii\helpers\Url;
use yii\web\Application;

/**
 *
 * @property string $clientSecret
 * @property string $clientId
 * @property-read string $redirectUrl
 * @property bool $active
 */
class OAuth2Behavior extends Behavior
{
    private ?string $_clientId = null;
    private ?string $_clientSecret = null;
    private ?string $_redirectUrl = null;

    /**
     * @throws InvalidConfigException
     */
    public function getClientId():string
    {
        if(empty($this->_clientId)) {
            if(isset($this->config->paramsGroup, \Yii::$app->params[$this->config->paramsGroup][$this->socialName]['clientId'])) {
                return \Yii::$app->params[$this->config->paramsGroup][$this->socialName]['clientId'];
            }
            throw new InvalidConfigException('Not param "clientId" to social "' . $this->socialName . '"');
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
            if(isset($this->config->paramsGroup, \Yii::$app->params[$this->config->paramsGroup][$this->socialName]['clientSecret'])) {
                return \Yii::$app->params[$this->config->paramsGroup][$this->socialName]['clientSecret'];
            }
            throw new InvalidConfigException('Not param "clientSecret" to social "' . $this->socialName. '"');
        }
        return $this->_clientSecret;
    }
    public function setClientSecret(string $value): void
    {
        $this->_clientSecret = $value;
    }

    public function getRedirectUrl()  : string
    {
        if(\Yii::$app instanceof Application) {
            return Url::toRoute([
                "{$this->configure->route}/handler",
                'social' => $this->socialName,
            ], true);
        }
    }
}