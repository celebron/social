<?php

namespace Celebron\social\socials;

use Celebron\social\attrs\WidgetSupport;
use Celebron\social\interfaces\FullUrlInterface;
use Celebron\social\AbstractOAuth2;
use Celebron\social\RequestCode;
use Celebron\social\RequestId;
use Celebron\social\RequestToken;
use Celebron\social\SocialResponse;
use yii\base\InvalidConfigException;
use yii\httpclient\Exception;
use yii\httpclient\Request;
use yii\web\BadRequestHttpException;
use Yiisoft\Http\Header;

/**
 *
 *
 *
 * @property-read string $uriToken
 * @property-read string $uriInfo
 * @property-read string $baseUrl
 * @property-read string $uriCode
 * @property-write Request $fullUrl
 */
#[WidgetSupport(true, true)]
class Discord extends AbstractOAuth2 implements FullUrlInterface//, ToWidgetInterface
{
    public array $scope = [ 'identify' ];

    public string $_icon = '';
    public ?string $_name;
    public bool $_visible = true;

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
    public function requestId (RequestId $request): SocialResponse
    {

        $url = $request->get(
            [ Header::AUTHORIZATION => $request->getTokenTypeToken()],
            [ 'format'=>'json' ],
        );

        return $this->sendResponse($url, 'user.id');
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
            $url .= http_build_query($params, null,null, PHP_QUERY_RFC3986);
        }

        return $url;
    }

    public function getUriInfo (): string
    {
        return 'oauth2/@me';
    }

    public function getBaseUrl (): string
    {
        return 'https://discord.com/api';
    }

    public function getUriCode (): string
    {
        return 'oauth2/authorize';
    }

    public function getUriToken (): string
    {
       return 'oauth2/token';
    }

    public function getUriRefreshToken (): string
    {
        return '';
    }
}