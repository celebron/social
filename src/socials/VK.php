<?php

namespace Celebron\social\socials;

use Celebron\social\SocialOAuth;

class VK extends SocialOAuth
{
    public string $clientUrl = 'https://oauth.vk.com';
    public string $display = 'page';

    protected function requestCode ()
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