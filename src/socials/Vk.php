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
        $this->sendRedirect($link);
        exit();
    }

}
