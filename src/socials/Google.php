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
#[WidgetSupport(true, true)]
class Google extends OAuth2 implements GetUrlsInterface, ToWidgetInterface
{
    use ToWidgetTrait;
    private string $authUrl = 'https://accounts.google.com/o/oauth2/auth';
    private string $tokenUrl = 'https://oauth2.googleapis.com/token';
    private string $apiUrl = 'https://www.googleapis.com';
    private string $uriInfo = 'oauth2/v2/userinfo?alt=json';

    public string $_icon = '';
    public ?string $_name;
    public bool $_visible = true;

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
    public function requestId (RequestId $request): Response
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

}