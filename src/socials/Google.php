<?php

namespace Celebron\social\socials;

use yii\base\InvalidArgumentException;
use yii\helpers\Json;
use Yiisoft\Http\Header;


/**
 * oauth2 Google
 * @property-write string $configFile
 */
class Google extends \Celebron\social\SocialOAuth2
{
    public string $authUrl = 'https://accounts.google.com/o/oauth2/auth';
    public string $tokenUrl = 'https://oauth2.googleapis.com/token';
    public string $apiUrl = 'https://www.googleapis.com';

    /**
     * Получения конфигурации из файла json
     * @param string $file
     * @return void
     */
    public function setConfigFile(string $file): void
    {
        $config = \Yii::getAlias($file);

        if (!$config && !file_exists($config)) {
            throw new InvalidArgumentException(sprintf('file "%s" does not exist', $config));
        }

        $json = file_get_contents($config);
        $config = Json::decode($json);

        if(isset($config['web'])) {
            $config = $config['web'];
        }

        $this->clientId = $config['client_id'];
        $this->clientSecret = $config['client_secret'];
        $this->authUrl = $config['auth_uri'];
        $this->tokenUrl = $config['token_uri'];

    }

    protected function requestCode ()
    {
        $this->getCode($this->authUrl,['access_type' => 'online', 'scope'=>'profile']);
        exit();
    }

    protected function requestId (): mixed
    {
        $data = $this->getToken($this->tokenUrl);
        $url = $this->client->get($this->apiUrl . '/oauth2/v2/userinfo?alt=json',
            [ 'format'=>'json' ],
            [ Header::AUTHORIZATION => $data->data['token_type'] . ' ' . $data->data['access_token'] ]);
        $d = $this->send($url);
        return $d->data['id'];
    }
}