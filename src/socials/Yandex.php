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
    public function requestCode ()
    {
        $get = Yii::$app->request->get();
        if (isset($get['error'])) {
            throw new BadRequestHttpException("[Yandex]Error: {$get['error']}. {$get['error_description']}");
        }

        $this->getCode('authorize');
        exit();
    }

}
