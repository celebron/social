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
    public string $icon = 'https://yastatic.net/s3/doc-binary/freeze/ru/id/228a1baa2a03e757cdee24712f4cc6b2e75636f2.svg';

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