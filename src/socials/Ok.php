<?php

namespace Celebron\social\socials;

use Celebron\social\SocialOAuth2;
use yii\web\BadRequestHttpException;

/**
 * Oauth2 Ok
 */
class Ok extends SocialOAuth2
{
    public string $scope = 'VALUABLE_ACCESS';

    public string $clientPublic;

    public string $clientCodeUrl = 'https://connect.ok.ru/oauth/authorize';
    public string $clientApiUrl = 'https://api.ok.ru';

    protected function requestCode () : void
    {
        $this->getCode($this->clientCodeUrl, ['scope'=> $this->scope]);
    }

    protected function sig(array $params, $token)
    {
        $secret = md5($token . $this->clientSecret);
        return md5(http_build_query($params,arg_separator: '') . $secret);
    }

    protected function requestId (): mixed
    {

        $this->clientUrl = $this->clientApiUrl;
        $data = $this->getToken('oauth/token.do');

        $params = [
            'application_key' => $this->clientPublic,
            'format' => 'json',
            'fields' => 'uid',
        ];

        ksort($params);
        $params['sig'] = $this->sig($params, $data->data['access_token']);
        $params['access_token'] = $data->data['access_token'];

        $postInfo = $this->client->post('api/users/getCurrentUser', $params);
        $dataInfo = $this->send($postInfo);
        if(isset($dataInfo->data['error_code'])) {
            $error = $dataInfo->getData();
            throw new BadRequestHttpException('[' . static::socialName() . "] E{$error['error_code']}.\n{$error['error_msg']}");
        }

        return $dataInfo->data['uid'];
    }
}