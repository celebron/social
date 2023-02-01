<?php

namespace Celebron\social;

use Celebron\social\interfaces\GetUrlsInterface;
use http\Exception\InvalidArgumentException;
use yii\base\BaseObject;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;


/**
 *
 * @property null|array $state
 */
class RequestCode extends BaseObject
{
    public string $response_type = 'code';
    public string $client_id;
    public string $redirect_uri;
    public string $uri;

    public array $data = [];


    public ?array $state;

    /**
     * @throws Exception
     */
    public function __construct (OAuth2 $social, array $config = [])
    {
        parent::__construct($config);
        $this->uri = ($social instanceof  GetUrlsInterface) ? $social->getUriCode() : '';
        $this->client_id = $social->clientId;
        $this->redirect_uri = $social->redirectUrl;
        $this->state =  self::stateDecode($social->state);
    }

    public function generateUri() : array
    {
        $default = [
            0 => $this->uri,
            'response_type' => $this->response_type,
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'state' => self::stateEncode($this->state),
        ];
        return ArrayHelper::merge($default, $this->data);
    }

    /**
     * @param string|null $state
     * @return array
     */
    public static function stateDecode(?string $state) : array
    {
        $data = [
            'm' => null,
            's' => null,
            'r' => null,
        ];

        if($state !== null) {
            $data = Json::decode(base64_decode($state));
        }

        return $data;
    }

    public static function stateEncode(array $state) : string
    {
        $data = [
            'm' => null,
            's' => null,
            'r' => null
        ];
        $data = ArrayHelper::merge($data, $state);
        if($data['r'] === null) {
            $data['r'] = \Yii::$app->security->generateRandomString();
        }
        return  base64_encode(Json::encode($data));
    }
}