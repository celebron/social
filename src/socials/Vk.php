<?php
namespace Celebron\social\socials;

use Celebron\social\SocialOAuth;
use VK\Exceptions\VKClientException;
use VK\Exceptions\VKOAuthException;
use yii\httpclient\{ Request, Response };
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use VK\OAuth\{VKOAuth, VKOAuthDisplay, VKOAuthResponseType};


/**
 *
 * @property-read Request|Response $link
 */
class Vk extends SocialOAuth  //implements iSocialError
{
    public array $scope;

    /**
     * @param $attribute
     * @param $params
     * @return int
     * @throws VKClientException
     * @throws VKOAuthException
     */
    public function requestId(string $code) : mixed
    {
        $oauth = new VKOAuth();
        $this->data = $oauth->getAccessToken($this->clientId, $this->clientSecret, $this->redirectUrl,$code);
        return (int)$this->data['user_id'];
    }

    /**
     * @param $data
     * @param bool $err
     * @return Response
     */
    public function getLink(string $state):Request
    {
        $oauth = new VKOAuth();
        $link = $oauth->getAuthorizeUrl(VKOAuthResponseType::CODE,
            $this->clientId,
            $this->redirectUrl,
            VKOAuthDisplay::PAGE,
            $this->scope,
            $state);
        return $this->getClient()->get($link);
    }

    /**
     * @return mixed
     */
    public function requestCode (string $state) : void
    {
        $link = $this->getLink($state);
        $data = $this->send($link, 'code', true);

        if($data->isOk) {
           $this->redirect($link);
            exit;
        }

        throw new BadRequestHttpException("[VK] Ошибка отправки кода");
    }

    /**
     * @param Controller $controller
     * @return \yii\web\Response
     */
    function error (Controller $controller): \yii\web\Response
    {
        \Yii::$app->session->setFlash('warning',\Yii::t('app','[{state}] User {user} not registred!',[
            'state' => static::getSocialName(),
            'user'=> $this->id,
        ]));
        return $controller->goBack();
    }

    public function registerSuccess (Controller $controller): \yii\web\Response
    {
        \Yii::$app->session->setFlash('success',\Yii::t('app','Association with Vkontakte - Done'));
        return $controller->goBack();
    }

}
