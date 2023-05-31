<?php

namespace Celebron\social\socials;

use Celebron\social\interfaces\GetUrlsInterface;
use Celebron\social\OAuth2;
use Celebron\social\RequestCode;
use Celebron\social\RequestId;
use Celebron\social\RequestToken;


/**
 * Oauth2 VK
 */
class VK extends OAuth2 implements GetUrlsInterface
{
    public string $display = 'page';

    private string $_icon = '';
    private ?string $_name;
    private bool $_visible = true;

    public function requestCode (RequestCode $request) : void
    {
        $request->data = [ 'display' => $this->display ];
    }


    public function requestToken (RequestToken $request): void
    {

    }

    public function requestId (RequestId $request): \Celebron\social\Response
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
}