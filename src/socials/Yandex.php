<?php /** @noinspection MissedFieldInspection */


namespace Celebron\social\socials;


use Celebron\social\SocialOAuth;
use Yii;
use yii\base\InvalidConfigException;
use yii\httpclient\Exception;
use yii\web\BadRequestHttpException;
use Yiisoft\Http\Header;

/**
 * Class Yandex
 * @package common\models\auth
 *
 * @property-read string|mixed $baseUrl
 */
class Yandex extends SocialOAuth
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
        $oauthData = $this->getToken('token', [], [ Header::AUTHORIZATION => 'Basic ' . base64_encode($text)]);

        if($oauthData->isOk) {
            $login = $this->getClient()->get(
                'https://login.yandex.ru/info',
                [ 'format'=>'json' ],
                [ Header::AUTHORIZATION => 'OAuth ' . $oauthData->data['access_token'] ]
            );

            $loginData = $this->send($login, true);
            return $loginData->data['id'];

        }
        $data = $oauthData->getData();
        throw new BadRequestHttpException("[Yandex]Error: {$data['error']}. {$data['error_description']}");
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
