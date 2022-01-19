<?php


namespace Celebron\social\socials;

use Celebron\social\SocialBase;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\Response;
use yii\web\BadRequestHttpException;

/**
 *
 * @property-write string $configFile
 * @property-read \Google_Client $googleClient
 */
class Google extends SocialBase
{
    private ?\Google_Client $_googleClient = null;

    /**
     * @return \Google_Client
     */
    public function getGoogleClient(): \Google_Client
    {
        if($this->_googleClient === null) {
            $this->_googleClient = new \Google_Client();
            $this->_googleClient->setApplicationName("Celebron APP | Auth service");
            $this->_googleClient->addScope("email");
            $this->_googleClient->addScope("profile");
        }
        return $this->_googleClient;
    }


    public function setConfigFile(string $path)
    {
        $this->getGoogleClient()->setAuthConfig(\Yii::getAlias($path));
    }

    /**
     * @inheritDoc
     */
    public function requestId (string $code) : mixed
    {
        $token = $this->getGoogleClient()->fetchAccessTokenWithAuthCode($code);
        //Если нету токина, то вернуть назад
        if(empty($token['access_token']))  {
            throw new BadRequestHttpException('[' . static::getSocialName() . "]Access token not received");
        }
        $this->getGoogleClient()->setAccessToken($token['access_token']);
        $google_oauth = new \Google_Service_Oauth2($this->getGoogleClient());
        $this->data = $google_oauth->userinfo->get();
        return $this->data->id;
    }

    public function rules (): array
    {
        return ArrayHelper::merge(parent::rules(),[
           ['configFile', 'required'],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function requestCode (string $state) : void
    {
        $this->getGoogleClient()->setState($state);
        $this->redirect($this->getGoogleClient()->createAuthUrl());
        exit;
    }
}
