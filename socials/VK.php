<?php

namespace Celebron\socials;


use Celebron\socialSource\interfaces\UrlsInterface;
use Celebron\socialSource\interfaces\ViewerInterface;
use Celebron\socialSource\OAuth2;
use Celebron\socialSource\requests\CodeRequest;
use Celebron\socialSource\requests\IdRequest;
use Celebron\socialSource\requests\TokenRequest;
use Celebron\socialSource\ResponseSocial;

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
 * @property-read string $uriToken
 */
class VK extends OAuth2 implements UrlsInterface, ViewerInterface
{
    public string $display = 'page';

    public function requestCode (CodeRequest $request) : void
    {
        $request->data = [ 'display' => $this->display ];
    }


    public function requestToken (TokenRequest $request): void
    {

    }

    public function requestId (IdRequest $request): ResponseSocial
    {
        return $this->response('user_id', $request->getTokenData());
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