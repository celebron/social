<?php

namespace Celebron\social\socials;

use Celebron\social\SocialOAuth2;

/**
 * Oauth2 VK
 */
class VK extends SocialOAuth2
{
    public string $clientUrl = 'https://oauth.vk.com';
    public string $display = 'page';


    protected function requestCode () : void
    {
        $this->getCode('authorize',[ 'display' => $this->display ]);
        exit;
    }

    protected function requestId (): mixed
    {
        $data = $this->getToken('access_token');
        return $data->data['user_id'];
    }
}