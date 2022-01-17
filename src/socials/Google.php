<?php


namespace Celebron\social\socials;

use Celebron\social\SocialBase;
use yii\web\Controller;
use yii\web\Response;

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
        $this->getGoogleClient()->setAccessToken($token['access_token']);
        $google_oauth = new \Google_Service_Oauth2($this->getGoogleClient());
        $this->data = $google_oauth->userinfo->get();
        return $this->data->id;
    }

    /**
     * @inheritDoc
     */
    public function requestCode () : void
    {
        $this->getGoogleClient()->setState($this->state);
        $this->redirect($this->getGoogleClient()->createAuthUrl());
        exit;
    }

    public function registerSuccess (Controller $controller): Response
    {
        \Yii::$app->session->setFlash('success',\Yii::t('app','Association with Google - Done'));
        return $controller->goBack();
    }

}