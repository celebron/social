<?php /*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\socials;

use Celebron\common\Token;
use Celebron\source\social\interfaces\UrlsInterface;
use Celebron\source\social\interfaces\ViewerInterface;
use Celebron\source\social\OAuth2;
use Celebron\source\social\data\CodeData;
use Celebron\source\social\data\IdData;
use Celebron\source\social\data\TokenData;
use Celebron\source\social\responses\Code;
use Celebron\source\social\responses\Id;
use Celebron\source\social\traits\ViewerTrait;
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
 * @property-read bool $supportManagement
 * @property-read string $baseUrl
 */
class Yandex extends OAuth2 implements UrlsInterface, ViewerInterface
{
    use ViewerTrait;
    public ?string $fileName = null;


    /**
     * @throws BadRequestHttpException
     */
    public function requestId (IdData $request): Id
    {
        $request->getHeaderOauth(['format'=>'json']);
        return $request->responseId('id');
    }

    public function defaultIcon():string
    {
        return "@public/yandex.png";
    }

    /**
     * @throws BadRequestHttpException
     */
    public function requestCode (CodeData $request) : Code
    {
        return $request->request();
    }


    public function requestToken (TokenData $request): Token
    {
        $request->setHeaderAuthorizationBasic($this->clientId . ':' . $this->clientSecret);
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
