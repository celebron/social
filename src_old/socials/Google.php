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
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
use yii\httpclient\Exception;
use yii\web\BadRequestHttpException;
use Yiisoft\Http\Header;


/**
 * oauth2 Google
 * @property-read string $uriToken
 * @property-read string $baseUrl
 * @property-read string $uriCode
 * @property-write string $configFile
 */
#[WidgetSupport]
class Google extends Social implements GetUrlsInterface, ToWidgetInterface
{
    use ToWidgetTrait;
    public string $authUrl = 'https://accounts.google.com/o/oauth2/auth';
    public string $tokenUrl = 'https://oauth2.googleapis.com/token';
    public string $apiUrl = 'https://www.googleapis.com';
    public string $uriInfo = 'oauth2/v2/userinfo?alt=json';

    public string $icon = '';
    public ?string $name;
    public bool $visible = true;

    /**
     * Получения конфигурации из файла json
     * @param string $file
     * @return void
     */
    public function setConfigFile(string $file): void
    {
        $config = \Yii::getAlias($file);

        if (!$config && !file_exists($config)) {
            throw new InvalidArgumentException(sprintf('file "%s" does not exist', $config));
        }

        $json = file_get_contents($config);
        $config = Json::decode($json);

        if(isset($config['web'])) {
            $config = $config['web'];
        }

        $this->clientId = $config['client_id'];
        $this->clientSecret = $config['client_secret'];
        $this->authUrl = $config['auth_uri'];
        $this->tokenUrl = $config['token_uri'];

    }

    public function requestCode (RequestCode $request) : void
    {
        $request->data = ['access_type' => 'online', 'scope'=>'profile'];
    }

    public function requestToken (RequestToken $request): void
    {

    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    public function requestId (RequestId $request): mixed
    {
        $url = $request->get(
            [ Header::AUTHORIZATION => $request->getTokenTypeToken() ],
            [ 'format'=>'json' ],
        );
        return $this->sendToField($url, 'id');
        //return $d->data['id'];
    }

    public function getBaseUrl (): string
    {
        return $this->apiUrl;
    }

    public function getUriCode (): string
    {
       return $this->authUrl;
    }

    public function getUriToken (): string
    {
       return $this->tokenUrl;
    }

    public function getUriInfo (): string
    {
        return $this->uriInfo;
    }

}