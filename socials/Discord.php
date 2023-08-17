<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\socials;

use Celebron\common\Token;
use Celebron\source\social\interfaces\UrlFullInterface;
use Celebron\source\social\interfaces\UrlsInterface;
use Celebron\source\social\interfaces\ViewerInterface;
use Celebron\source\social\OAuth2;
use Celebron\source\social\data\CodeData;
use Celebron\source\social\data\IdData;
use Celebron\source\social\data\TokenData;
use Celebron\source\social\responses\Code;
use Celebron\source\social\responses\Id;
use yii\httpclient\Request;
use yii\web\BadRequestHttpException;
use Yiisoft\Http\Header;

/**
 *
 * @property null|string $icon
 * @property string $name
 * @property bool $visible
 *
 * @property-read string $uriToken
 * @property-read string $uriInfo
 * @property-read string $baseUrl
 * @property-read string $uriCode
 * @property-read bool $supportRegister
 * @property-read bool $supportLogin
 * @property-read bool $supportManagement
 * @property-write Request $fullUrl
 */
class Discord extends OAuth2 implements UrlsInterface, UrlFullInterface, ViewerInterface
{
    public array $scope = [ 'identify' ];

    public function requestCode (CodeData $request) : Code
    {
        return $request->request(['scope' => implode(' ', $this->scope)]);
    }

    public function requestToken (TokenData $request): Token
    {
        return $request->responseToken();
    }

    /**
     * @throws BadRequestHttpException
     */
    public function requestId (IdData $request): Id
    {
        $request->get(
            [ Header::AUTHORIZATION => $request->getTokenTypeToken()],
            [ 'format'=>'json' ],
        );
        return $request->responseId('user.id');
    }

    public function fullUrl(Request $request) : string
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

    public function getSupportManagement (): bool
    {
        return true;
    }

    public function getSupportLogin (): bool
    {
       return true;
    }
}