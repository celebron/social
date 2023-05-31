<?php

namespace Celebron\social\old\socials;

use Celebron\social\old\interfaces\GetUrlsInterface;
use Celebron\social\old\interfaces\RequestIdInterface;
use Celebron\social\old\interfaces\ToWidgetInterface;
use Celebron\social\old\interfaces\ToWidgetLoginInterface;
use Celebron\social\old\interfaces\ToWidgetRegisterInterface;
use Celebron\social\old\interfaces\ToWidgetTrait;
use Celebron\social\old\RequestCode;
use Celebron\social\old\RequestId;
use Celebron\social\old\RequestToken;
use Celebron\social\old\Social;
use Celebron\social\old\WidgetSupport;
use yii\base\InvalidConfigException;
use yii\httpclient\Exception;
use yii\web\BadRequestHttpException;

/**
 * Oauth2 Ok
 */
#[WidgetSupport]
class Ok extends Social implements GetUrlsInterface, RequestIdInterface, ToWidgetInterface
{
    use ToWidgetTrait;
    public string $scope = 'VALUABLE_ACCESS';

    public string $clientPublic;

    public string $clientCodeUrl = 'https://connect.ok.ru/oauth/authorize';
    public string $clientApiUrl = 'https://api.ok.ru';
    public string $uriToken = 'oauth/token.do';
    public string $uriInfo = 'api/users/getCurrentUser';

    public string $icon = '';
    public ?string $name;
    public bool $visible = true;

    public function requestCode (RequestCode $request) : void
    {
        $request->data['scope'] = $this->scope;
    }

    public function requestToken (RequestToken $request): void
    {

    }

    protected function sig(array $params, $token)
    {
        $secret = md5($token . $this->clientSecret);
        return md5(http_build_query($params,arg_separator: '') . $secret);
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    public function requestId (RequestId $request): mixed
    {

        $params = [
            'application_key' => $this->clientPublic,
            'format' => 'json',
            'fields' => 'uid',
        ];

        $token = $request->getAccessToken();
        ksort($params);
        $params['sig'] = $this->sig($params, $token);
        $params['access_token'] = $token;

        $postInfo = $request->post($params);
        $dataInfo = $this->send($postInfo);
        if(isset($dataInfo->data['error_code'])) {
            $error = $dataInfo->getData();
            throw new BadRequestHttpException('[' . static::socialName() . "] E{$error['error_code']}.\n{$error['error_msg']}");
        }

        return $dataInfo->data['uid'];
    }

    public function getBaseUrl (): string
    {
        return $this->clientApiUrl;
    }

    public function getUriCode (): string
    {
        return $this->clientCodeUrl;
    }

    public function getUriToken (): string
    {
        return $this->uriToken;
    }

    public function getUriInfo (): string
    {
        return $this->uriInfo;
    }
}