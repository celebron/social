<?php

namespace Celebron\social\socials;

use Celebron\social\interfaces\GetUrlsInterface;
use Celebron\social\interfaces\GetUrlsTrait;
use Celebron\social\interfaces\ToWidgetInterface;
use Celebron\social\interfaces\ToWidgetLoginInterface;
use Celebron\social\interfaces\ToWidgetRegisterInterface;
use Celebron\social\interfaces\ToWidgetTrait;
use Celebron\social\RequestCode;
use Celebron\social\RequestToken;
use Celebron\social\Social;

/**
 * Oauth2 VK
 */
class VK extends Social implements GetUrlsInterface, ToWidgetInterface, ToWidgetLoginInterface, ToWidgetRegisterInterface
{
    use ToWidgetTrait, GetUrlsTrait;
    public string $clientUrl = 'https://oauth.vk.com';
    public string $uriCode = 'authorize';
    public string $uriToken = 'access_token';
    public string $display = 'page';

    public string $icon = '';
    public ?string $name;
    public bool $visible = true;

    protected function requestCode (RequestCode $request) : void
    {
        $request->data = [ 'display' => $this->display ];
    }

    protected function requestToken (RequestToken $request): void
    {
        $response = $this->send($request);
        $this->id = $response->data['user_id'];
    }
}