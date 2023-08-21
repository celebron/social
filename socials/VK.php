<?php
/*
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
 * Oauth2 VK
 *
 * @property null|string $icon
 * @property string $name
 * @property bool $visible
 *
 * @property-read string $uriCode
 * @property-read string $baseUrl
 * @property-read string $uriInfo
 * @property-read bool $supportLogin
 * @property-read bool $supportManagement
 * @property-read string $uriToken
 */
class VK extends OAuth2 implements UrlsInterface, ViewerInterface
{
    use ViewerTrait;
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
     */
    public function requestId (IdData $request): Id
    {
        return $this->responseId('user_id', $request->token->data);
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