<?php

namespace Celebron\social\old\socials;

use Celebron\social\old\interfaces\GetUrlsInterface;
use Celebron\social\old\interfaces\GetUrlsTrait;
use Celebron\social\old\interfaces\RequestIdInterface;
use Celebron\social\old\interfaces\SetFullUrlInterface;
use Celebron\social\old\interfaces\ToWidgetInterface;
use Celebron\social\old\interfaces\ToWidgetLoginInterface;
use Celebron\social\old\interfaces\ToWidgetRegisterInterface;
use Celebron\social\old\interfaces\ToWidgetTrait;
use Celebron\social\old\RequestCode;
use Celebron\social\old\RequestId;
use Celebron\social\old\RequestToken;
use Celebron\social\old\Social;
use Celebron\social\old\WidgetSupport;
use yii\base\InvalidConfigException;
use yii\httpclient\Exception;
use yii\httpclient\Request;
use yii\web\BadRequestHttpException;
use Yiisoft\Http\Header;

/**
 *
 *
 */
#[WidgetSupport]
class Discord extends Social
    implements GetUrlsInterface, RequestIdInterface, ToWidgetInterface, SetFullUrlInterface
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

    public function requestCode (RequestCode $request) : void
    {
        $request->data = ['scope' => implode(' ', $this->scope)];
    }

    public function requestToken (RequestToken $request): void
    {

    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    public function requestId (RequestId $request): mixed
    {

        $url = $request->get(
            [ Header::AUTHORIZATION => $request->getTokenTypeToken()],
            [ 'format'=>'json' ],
        );

        return $this->sendToField($url, 'user.id');
        //return $data->data['user']['id'];
    }

    public function setFullUrl(Request $request) : string
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
            if (!str_contains($url, '?')) {
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