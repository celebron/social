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
        if(\Yii::$app instanceof Application) {
            return Url::toRoute([
                "{$this->configure->route}/handler",
                'social' => $this->socialName,
            ], true);
        }
        if(!empty($this->_redirectUrl)) {
            return $this->_redirectUrl;
        }
        throw new InvalidConfigException('Param "redirectUrl" to social "' . $this->socialName . '" empty');
    }

    public function setRedirectUrl(string $value): void
    {
        $this->_redirectUrl = $value;
    }
}