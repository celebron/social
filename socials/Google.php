<?php

namespace Celebron\socials;

use Celebron\socialSource\interfaces\UrlsInterface;
use Celebron\socialSource\interfaces\ViewerInterface;
use Celebron\socialSource\OAuth2;
use Celebron\socialSource\requests\CodeRequest;
use Celebron\socialSource\requests\IdRequest;
use Celebron\socialSource\requests\TokenRequest;
use Celebron\socialSource\ResponseSocial;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
use yii\httpclient\Exception;
use yii\web\BadRequestHttpException;
use Yiisoft\Http\Header;


/**
 * oauth2 Google
 * @property null|string $icon
 * @property string $name
 * @property bool $visible
 *
 * @property-read string $uriToken
 * @property-read string $baseUrl
 * @property-read string $uriCode
 * @property-read bool $supportRegister
 * @property-read bool $supportLogin
 * @property-write string $configFile
 */
class Google extends OAuth2 implements UrlsInterface, ViewerInterface
{
    private string $authUrl = 'https://accounts.google.com/o/oauth2/auth';
    private string $tokenUrl = 'https://oauth2.googleapis.com/token';
    private string $apiUrl = 'https://www.googleapis.com';
    private string $uriInfo = 'oauth2/v2/userinfo?alt=json';


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

    public function requestCode (CodeRequest $request) : void
    {
        $request->data = ['access_type' => 'online', 'scope'=>'profile'];
    }

    public function requestToken (TokenRequest $request): void
    {

    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    public function requestId (IdRequest $request): ResponseSocial
    {
        $url = $request->get(
            [ Header::AUTHORIZATION => $request->getTokenTypeToken() ],
            [ 'format'=>'json' ],
        );
        return $this->sendResponse($url, 'id');
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

    public function getSupportRegister (): bool
    {
       return true;
    }

    public function getSupportLogin (): bool
    {
        return true;
    }
}