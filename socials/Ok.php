<?php

namespace Celebron\socials;

use Celebron\socialSource\interfaces\UrlsInterface;
use Celebron\socialSource\interfaces\ViewerInterface;
use Celebron\socialSource\OAuth2;
use Celebron\socialSource\requests\CodeRequest;
use Celebron\socialSource\requests\IdRequest;
use Celebron\socialSource\requests\TokenRequest;
use Celebron\socialSource\ResponseSocial;
use yii\base\InvalidConfigException;
use yii\httpclient\Exception;
use yii\web\BadRequestHttpException;

/**
 * Oauth2 Ok
 *
 * @property null|string $icon
 * @property string $name
 * @property bool $visible
 *
 * @property-read string $uriCode
 * @property-read string $baseUrl
 * @property-read string $uriInfo
 * @property-read bool $supportRegister
 * @property-read bool $supportLogin
 * @property-read string $uriToken
 */
class Ok extends OAuth2 implements UrlsInterface, ViewerInterface
{
    public string $scope = 'VALUABLE_ACCESS';

    public string $clientPublic;

    private string $_icon = '';
    private ?string $_name;
    private bool $_visible = true;

    public function requestCode (CodeRequest $request) : void
    {
        $request->data['scope'] = $this->scope;
    }

    public function requestToken (TokenRequest $request): void
    {

    }

    protected function sig(array $params, $token):string
    {
        $secret = md5($token . $this->clientSecret);
        return md5(http_build_query($params,arg_separator: '') . $secret);
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    public function requestId (IdRequest $request): ResponseSocial
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
            throw new BadRequestHttpException('[' . $this->socialName . "] E{$error['error_code']}.\n{$error['error_msg']}");
        }

        return $this->response('uid', $dataInfo->data); // $dataInfo->data['uid'];
    }

    public function getBaseUrl (): string
    {
        return  'https://api.ok.ru';
    }

    public function getUriCode (): string
    {
        return 'https://connect.ok.ru/oauth/authorize';
    }

    public function getUriToken (): string
    {
        return 'oauth/token.do';
    }

    public function getUriInfo (): string
    {
        return 'api/users/getCurrentUser';
    }

    public function getSupportManagement (): bool
    {
        return true;
    }

    public function getSupportLogin (): bool
    {
        return true;
    }
}