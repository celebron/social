<?php

namespace Celebron\social\socials;

use Celebron\social\attrs\WidgetSupport;
use Celebron\social\interfaces\GetUrlsInterface;
use Celebron\social\interfaces\ToWidgetInterface;
use Celebron\social\interfaces\ToWidgetTrait;
use Celebron\social\AbstractOAuth2;
use Celebron\social\RequestCode;
use Celebron\social\RequestId;
use Celebron\social\RequestToken;


/**
 * Oauth2 VK
 *
 * @property-read string $uriCode
 * @property-read string $baseUrl
 * @property-read string $uriInfo
 * @property-read string $uriToken
 */
#[WidgetSupport(true, true)]
class VK extends AbstractOAuth2 implements GetUrlsInterface, ToWidgetInterface
{
    use ToWidgetTrait;


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

    public function requestId (RequestId $request): \Celebron\social\SocialResponse
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