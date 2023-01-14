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
use yii\base\InvalidArgumentException;
use yii\helpers\Json;
use Yiisoft\Http\Header;


/**
 * oauth2 Google
 * @property-write string $configFile
 */
class Google extends Social implements GetUrlsInterface, RequestIdInterface, ToWidgetInterface, ToWidgetLoginInterface, ToWidgetRegisterInterface
{
    use ToWidgetTrait;
    public string $authUrl = 'https://accounts.google.com/o/oauth2/auth';
    public string $tokenUrl = 'https://oauth2.googleapis.com/token';
    public string $apiUrl = 'https://www.googleapis.com';
    public string $uriInfo = 'oauth2/v2/userinfo?alt=json';

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

    protected function requestCode (RequestCode $request) : void
    {
        $request->data = ['access_type' => 'online', 'scope'=>'profile'];
    }

    protected function requestToken (RequestToken $request): void
    {

    }

    public function requestId (RequestId $request): mixed
    {
        $url = $request->get(
            [ Header::AUTHORIZATION => $request->getTokenTypeToken() ],
            [ 'format'=>'json' ],
        );
        $d = $this->send($url);
        return $d->data['id'];
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