<?php /** @noinspection MissedFieldInspection */


namespace Celebron\social\socials;


use Celebron\social\SocialOAuth;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;
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
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function requestId (string $code) : mixed
    {
        $text = $this->clientId . ':' . $this->clientSecret;
        $oauthData = $this->getToken('token',$code, [], [ Header::AUTHORIZATION => 'Basic ' . base64_encode($text)]);

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
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function requestCode (string $state) : void
    {
        $get = \Yii::$app->request->get();
        if (isset($get['error'])) {
            throw new BadRequestHttpException("[Yandex]Error: {$get['error']}. {$get['error_description']}");
        }

//        $url = $this->getCodeUrl('authorize', $state);
//        $this->redirect($url);
        $this->responseCode('authorize', $state);
    }

}
