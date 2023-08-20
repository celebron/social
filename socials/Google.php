<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\socials;

use Celebron\source\social\interfaces\UrlsInterface;
use Celebron\source\social\interfaces\ViewerInterface;
use Celebron\source\social\OAuth2;
use Celebron\source\social\data\CodeData;
use Celebron\source\social\data\IdData;
use Celebron\source\social\data\TokenData;
use Celebron\source\social\responses\Code;
use Celebron\source\social\responses\Id;
use Celebron\source\social\traits\ViewerTrait;
use yii\base\InvalidArgumentException;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use Yiisoft\Http\Header;


/**
 * oauth2 Google
 * @deprecated Не тестировалось в новых версиях
 * @property null|string $icon
 * @property string $name
 * @property bool $visible
 *
 * @property-read string $uriToken
 * @property-read string $baseUrl
 * @property-read string $uriCode
 * @property-read bool $supportRegister
 * @property-read bool $supportLogin
 * @property-read bool $supportManagement
 * @property-write string $configFile
 */
class Google extends OAuth2 implements UrlsInterface, ViewerInterface
{
    use ViewerTrait;

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

    public function requestCode (CodeData $request) : Code
    {
        return $request->request(['access_type' => 'online', 'scope'=>'profile']);
    }

    public function requestToken (TokenData $request): \Celebron\common\Token
    {
        return $request->responseToken();
    }

    /**
     * @throws BadRequestHttpException
     */
    public function requestId (IdData $request): Id
    {
        $request->get(
            [ Header::AUTHORIZATION => $request->getTypedToken() ],
            [ 'format'=>'json' ],
        );
        return $request->responseId('id');
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

    public function getSupportManagement (): bool
    {
        return true;
    }

    public function getSupportLogin (): bool
    {
        return true;
    }
}