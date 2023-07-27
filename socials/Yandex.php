<?php /** @noinspection MissedFieldInspection */


namespace Celebron\socials;

use Celebron\socialSource\interfaces\UrlsInterface;
use Celebron\socialSource\interfaces\ViewerInterface;
use Celebron\socialSource\OAuth2;
use Celebron\socialSource\requests\CodeRequest;
use Celebron\socialSource\requests\IdRequest;
use Celebron\socialSource\requests\TokenRequest;
use Celebron\socialSource\ResponseSocial;
use Yii;
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
     * @throws Exception
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    public function requestId (IdRequest $request): ResponseSocial
    {
        $login = $request->getHeaderOauth(['format'=> 'json']);
        return $this->sendResponse($login, 'id');
    }

    /**
     * @throws BadRequestHttpException
     */
    public function requestCode (CodeRequest $request) : void
    {
        $get = Yii::$app->request->get();
        if (isset($get['error'])) {
            throw new BadRequestHttpException("[Yandex]Error: {$get['error']}. {$get['error_description']}");
        }
    }

    public function requestToken (TokenRequest $request): void
    {
        $request->setAuthorizationBasic($this->clientId . ':' . $this->clientSecret);
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
}
