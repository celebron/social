<?php

namespace Celebron\social\socials;

use Celebron\social\interfaces\GetUrlsInterface;
use Celebron\social\interfaces\GetUrlsTrait;
use Celebron\social\interfaces\RequestIdInterface;
use Celebron\social\interfaces\SetFullUrlInterface;
use Celebron\social\interfaces\ToWidgetInterface;
use Celebron\social\interfaces\ToWidgetLoginInterface;
use Celebron\social\interfaces\ToWidgetRegisterInterface;
use Celebron\social\interfaces\ToWidgetTrait;
use Celebron\social\RequestCode;
use Celebron\social\RequestId;
use Celebron\social\RequestToken;
use Celebron\social\Social;
use yii\httpclient\Request;
use Yiisoft\Http\Header;


class Discord extends Social
    implements GetUrlsInterface, RequestIdInterface, ToWidgetInterface, ToWidgetLoginInterface, ToWidgetRegisterInterface, SetFullUrlInterface
{
    use ToWidgetTrait, GetUrlsTrait;
    public string $clientUrl = 'https://discord.com/api';
    public string $uriToken = 'oauth2/token';
    public string $uriCode = 'oauth2/authorize';
    public string $uriInfo = 'oauth2/@me';
    public array $scope = [ 'identify' ];

    public string $icon = '';
    public ?string $name;
    public bool $visible = true;

    protected function requestCode (RequestCode $request) : void
    {
        $request->data = ['scope' => implode(' ', $this->scope)];
    }

    protected function requestToken (RequestToken $request): void
    {

    }

    public function requestId (RequestId $request): mixed
    {

        $url = $request->get(
            [ Header::AUTHORIZATION => $request->getTokenTypeToken()],
            [ 'format'=>'json' ],
        );

        $data = $this->send($url);
        return $data->data['user']['id'];
    }

    public function setFullUrl(Request $request)
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
            $url .= http_build_query($params, encoding_type: PHP_QUERY_RFC3986);
        }

        return $url;
    }

    public function getUriInfo (): string
    {
        return $this->uriInfo;
    }

}