<?php /** @noinspection MissedFieldInspection */


namespace Celebron\socials;

use Celebron\common\Token;
use Celebron\socialSource\interfaces\UrlsInterface;
use Celebron\socialSource\interfaces\ViewerInterface;
use Celebron\socialSource\OAuth2;
use Celebron\socialSource\data\CodeData;
use Celebron\socialSource\data\IdData;
use Celebron\socialSource\data\TokenData;
use Celebron\socialSource\responses\CodeRequest;
use Celebron\socialSource\responses\IdResponse;
use yii\base\InvalidConfigException;
use yii\httpclient\Exception;
use yii\web\BadRequestHttpException;


/**
 * @property null|string $icon
 * @property string $name
 * @property bool $visible
 *
 * @property-read string $uriToken
 * @property-read string $uriInfo
 * @property-read string $uriCode
 * @property-read bool $supportRegister
 * @property-read bool $supportLogin
 * @property-read string $baseUrl
 */
class Yandex extends OAuth2 implements UrlsInterface, ViewerInterface
{
    private string $_icon = 'https://yastatic.net/s3/doc-binary/freeze/ru/id/228a1baa2a03e757cdee24712f4cc6b2e75636f2.svg';

    public ?string $fileName = null;


    /**
     * @throws BadRequestHttpException
     */
    public function requestId (IdData $request): IdResponse
    {
        $request->getHeaderOauth(['format'=>'json']);
        return $request->responseId('id');
    }

    /**
     * @throws BadRequestHttpException
     */
    public function requestCode (CodeData $request) : CodeRequest
    {
        return $request->request();
    }


    public function requestToken (TokenData $request): Token
    {
        $request->setAuthorizationBasic($this->clientId . ':' . $this->clientSecret);
        return $request->responseToken();
    }

    public function getBaseUrl (): string
    {
        return "https://oauth.yandex.ru";
    }

    public function getUriCode (): string
    {
        return 'authorize';
    }

    public function getUriToken (): string
    {
        return 'token';
    }

    public function getUriInfo (): string
    {
        return 'https://login.yandex.ru/info';
    }

    public function getSupportManagement (): bool
    {
        return true;
    }

    public function getSupportLogin (): bool
    {
        return true;
    }

    public function defaultRedirectUrl (): string
    {
        return "https://oauth.yandex.ru/verification_code";
    }
}
