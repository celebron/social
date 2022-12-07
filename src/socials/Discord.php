<?php

namespace Celebron\social\socials;

use Celebron\social\SocialOAuth2;
use yii\httpclient\Request;
use Yiisoft\Http\Header;

/**
 * oauth2 Discord
 */
class Discord extends SocialOAuth2
{
    public string $clientUrl = 'https://discord.com/api';
    public array $scope = [ 'identify' ];

    protected function requestCode () : void
    {
        $this->getCode('oauth2/authorize',['scope'=>implode(' ', $this->scope)]);
        exit();
    }

    /**
     * @return mixed
     */
    protected function requestId (): mixed
    {
        $response = $this->getToken('oauth2/token');

        $url = $this->client->get('oauth2/@me',
            [ 'format'=>'json' ],
            [ Header::AUTHORIZATION => $response->data['token_type'] . ' ' . $response->data['access_token'] ]);

        $data = $this->send($url);
        return $data->data['user']['id'];
    }

    public function getCodeUrl(string $url, array $data=[]) : Request
    {
        $request = parent::getCodeUrl($url, $data);
        $request->setFullUrl($this->createFullUrl($request));
        return $request;
    }

    private function createFullUrl(Request $request)
    {
        $url = $request->getUrl();
        if (is_array($url)) {
            $params = $url;
            if (isset($params[0])) {
                $url = (string)$params[0];
                unset($params[0]);
            } else {
                $url = '';
            }
        }

        if (empty($url)) {
            $url = $request->client->baseUrl;
        } elseif (!preg_match('/^https?:\\/\\//i', $url)) {
            $url = rtrim($request->client->baseUrl, '/') . '/' . ltrim($url, '/');
        }

        if (!empty($params)) {
            if (strpos($url, '?') === false) {
                $url .= '?';
            } else {
                $url .= '&';
            }
            $url .= http_build_query($params, enc_type: PHP_QUERY_RFC3986);
        }

        return $url;
    }
}