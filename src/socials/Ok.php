<?php

namespace Celebron\social\socials;

use Celebron\social\attrs\WidgetSupport;
use Celebron\social\interfaces\GetUrlsInterface;
use Celebron\social\interfaces\ToWidgetInterface;
use Celebron\social\interfaces\ToWidgetTrait;
use Celebron\social\OAuth2;
use Celebron\social\RequestCode;
use Celebron\social\RequestId;
use Celebron\social\RequestToken;
use Celebron\social\Response;
use yii\base\InvalidConfigException;
use yii\httpclient\Exception;
use yii\web\BadRequestHttpException;

/**
 * Oauth2 Ok
 *
 * @property-read string $uriCode
 * @property-read string $baseUrl
 * @property-read string $uriInfo
 * @property-read string $uriToken
 */
#[WidgetSupport(true, true)]
class Ok extends OAuth2 implements GetUrlsInterface, ToWidgetInterface
{
    use ToWidgetTrait;
    public string $scope = 'VALUABLE_ACCESS';

    public string $clientPublic;

    private string $_icon = '';
    private ?string $_name;
    private bool $_visible = true;

    public function requestCode (RequestCode $request) : void
    {
        $request->data['scope'] = $this->scope;
    }

    public function requestToken (RequestToken $request): void
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
    public function requestId (RequestId $request): Response
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
}