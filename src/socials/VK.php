<?php

namespace Celebron\social\socials;

use Celebron\social\interfaces\GetUrlsInterface;
use Celebron\social\interfaces\GetUrlsTrait;
use Celebron\social\interfaces\ToWidgetInterface;
use Celebron\social\interfaces\ToWidgetLoginInterface;
use Celebron\social\interfaces\ToWidgetRegisterInterface;
use Celebron\social\interfaces\ToWidgetTrait;
use Celebron\social\RequestCode;
use Celebron\social\RequestToken;
use Celebron\social\Social;
use yii\base\InvalidConfigException;
use yii\httpclient\Exception;
use yii\web\BadRequestHttpException;

/**
 * Oauth2 VK
 */
class VK extends Social implements GetUrlsInterface, ToWidgetInterface, ToWidgetLoginInterface, ToWidgetRegisterInterface
{
    use ToWidgetTrait, GetUrlsTrait;
    public string $clientUrl = 'https://oauth.vk.com';
    public string $uriCode = 'authorize';
    public string $uriToken = 'access_token';
    public string $display = 'page';

    public string $icon = '';
    public ?string $name;
    public bool $visible = true;

    protected function requestCode (RequestCode $request) : void
    {
        $request->data = [ 'display' => $this->display ];
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    protected function requestToken (RequestToken $request): void
    {
        $this->id = $this->sendReturnId($request, 'user_id');
        //$this->id = $response->data['user_id'];
    }
}