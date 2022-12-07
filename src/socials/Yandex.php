<?php /** @noinspection MissedFieldInspection */


namespace Celebron\social\socials;


use Celebron\social\SocialOAuth2;
use Yii;
use yii\base\InvalidConfigException;
use yii\httpclient\Exception;
use yii\web\BadRequestHttpException;
use Yiisoft\Http\Header;

/**
 * oauth2 Yandex
 *
 * @property-read string|mixed $baseUrl
 */
class Yandex extends SocialOAuth2
{
    public string $clientUrl = "https://oauth.yandex.ru";
    public string $icon = 'https://yastatic.net/s3/doc-binary/freeze/ru/id/228a1baa2a03e757cdee24712f4cc6b2e75636f2.svg';

    /**
     * @return mixed
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function requestId () : mixed
    {
        $text = $this->clientId . ':' . $this->clientSecret;
        $oauthData = $this->getToken('token', [], [Header::AUTHORIZATION => 'Basic ' . base64_encode($text)]);

        $login = $this->getClient()->get(
            'https://login.yandex.ru/info',
            ['format' => 'json'],
            [ Header::AUTHORIZATION => 'OAuth ' . $oauthData->data['access_token'] ]
        );

        $loginData = $this->send($login);
        return $loginData->data['id'];

    }

    /**
     * @return void
     * @throws BadRequestHttpException
     */
    public function requestCode () : void
    {
        $get = Yii::$app->request->get();
        if (isset($get['error'])) {
            throw new BadRequestHttpException("[Yandex]Error: {$get['error']}. {$get['error_description']}");
        }

        $this->getCode('authorize');
        exit();
    }

}
