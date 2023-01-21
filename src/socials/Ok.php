<?php

namespace Celebron\social\socials;

use Celebron\social\interfaces\GetUrlsInterface;
use Celebron\social\interfaces\RequestIdInterface;
use Celebron\social\interfaces\ToWidgetInterface;
use Celebron\social\interfaces\ToWidgetLoginInterface;
use Celebron\social\interfaces\ToWidgetRegisterInterface;
use Celebron\social\interfaces\ToWidgetTrait;
use Celebron\social\RequestCode;
use Celebron\social\RequestId;
use Celebron\social\RequestToken;
use Celebron\social\Social;
use Celebron\social\WidgetSupport;
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

    protected function requestCode (RequestCode $request) : void
    {
        $request->data['scope'] = $this->scope;
    }

    protected function requestToken (RequestToken $request): void
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