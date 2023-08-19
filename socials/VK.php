<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\socials;


use Celebron\common\Token;
use Celebron\socialSource\interfaces\UrlsInterface;
use Celebron\socialSource\interfaces\ViewerInterface;
use Celebron\socialSource\OAuth2;
use Celebron\socialSource\data\CodeData;
use Celebron\socialSource\data\IdData;
use Celebron\socialSource\data\TokenData;
use Celebron\socialSource\responses\Code;
use Celebron\socialSource\responses\Id;
use yii\web\BadRequestHttpException;

/**
 * Oauth2 VK
 *
 * @property null|string $icon
 * @property string $name
 * @property bool $visible
 *
 * @property-read string $uriCode
 * @property-read string $baseUrl
 * @property-read string $uriInfo
 * @property-read bool $supportRegister
 * @property-read bool $supportLogin
 * @property-read bool $supportManagement
 * @property-read string $uriToken
 */
class VK extends OAuth2 implements UrlsInterface, ViewerInterface
{
    public string $display = 'page';

    /**
     * @throws BadRequestHttpException
     */
    public function requestCode (CodeData $request) : Code
    {
        return $request->request([ 'display' => $this->display ]);
    }


    /**
     * @throws BadRequestHttpException
     */
    public function requestToken (TokenData $request): Token
    {
        return $request->responseToken();
    }

    /**
     * @throws BadRequestHttpException
     */
    public function requestId (IdData $request): Id
    {
        return $request->responseId('user_id');
    }

    public function getBaseUrl (): string
    {
        return 'https://oauth.vk.com';
    }

    public function getUriCode (): string
    {
        return 'authorize';
    }

    public function getUriToken (): string
    {
        return 'access_token';
    }

    public function getUriInfo (): string
    {
        return '';
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
        return "https://oauth.vk.com/blank.html";
    }
}