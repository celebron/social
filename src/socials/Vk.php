<?php
namespace Celebron\social\socials;

use Celebron\social\SocialOAuth;
use VK\Exceptions\VKClientException;
use VK\Exceptions\VKOAuthException;
use yii\httpclient\{Exception, Request, Response};
use VK\OAuth\{VKOAuth, VKOAuthDisplay, VKOAuthResponseType};
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;


/**
 *
 * @property-read Request|Response $link
 */
class Vk extends SocialOAuth
{
    public string $clientUrl = '';
    public array $scope;

    /**
     * @return int
     * @throws VKClientException
     * @throws VKOAuthException
     */
    public function requestId() : mixed
    {
        $oauth = new VKOAuth();
        $this->data = $oauth->getAccessToken($this->clientId, $this->clientSecret, $this->redirectUrl,$this->code);
        return $this->data['user_id'];
    }

    /**
     * @return Request
     */
    public function getLink():Request
    {
        $oauth = new VKOAuth();
        $link = $oauth->getAuthorizeUrl(VKOAuthResponseType::CODE,
            $this->clientId,
            $this->redirectUrl,
            VKOAuthDisplay::PAGE,
            $this->scope,
            $this->state);
        return $this->getClient()->get($link);
    }

    /**
     * @throws InvalidConfigException
     * @throws Exception
     * @throws BadRequestHttpException
     */
    public function requestCode ()
    {
        $link = $this->getLink();
        $this->sendRedirect($link);
        exit();
    }

}
