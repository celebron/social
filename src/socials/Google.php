<?php


namespace Celebron\social\socials;

use Celebron\social\Social;
use Google\Exception;
use Google_Client;
use Google_Service_Oauth2;
use Yii;
use yii\web\BadRequestHttpException;

/**
 *
 * @property-write string $configFile
 * @property-read Google_Client $googleClient
 */
class Google extends Social
{
    private ?Google_Client $_googleClient = null;

    /**
     * @return Google_Client
     */
    public function getGoogleClient(): Google_Client
    {
        if($this->_googleClient === null) {
            $this->_googleClient = new Google_Client();
            $this->_googleClient->setApplicationName("Celebron/social");
            $this->_googleClient->addScope("email");
            $this->_googleClient->addScope("profile");
        }
        return $this->_googleClient;
    }


    /**
     * @throws Exception
     */
    public function setConfigFile(string $path): void
    {
        $this->getGoogleClient()->setAuthConfig(Yii::getAlias($path));
    }


    /**
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function requestId () : mixed
    {
        $token = $this->getGoogleClient()->fetchAccessTokenWithAuthCode($this->code);
        //Если нету токина, то вернуть назад
        if(empty($token['access_token']))  {
            throw new BadRequestHttpException('[' . static::socialName() . "]Access token not received");
        }
        $this->getGoogleClient()->setAccessToken($token['access_token']);
        $google_oauth = new Google_Service_Oauth2($this->getGoogleClient());
        $this->data['info'] = $google_oauth->userinfo->get();
        return $this->data['info']->id;
    }


    /**
     * @return void
     */
    public function requestCode ()
    {
        $this->getGoogleClient()->setState($this->state);
        $this->redirect($this->getGoogleClient()->createAuthUrl());
        exit();
    }
}
