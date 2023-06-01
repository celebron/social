<?php

namespace Celebron\social\socials;

use Celebron\social\args\RequestArgs;
use Celebron\social\AuthBase;
use Celebron\social\Response;
use Celebron\social\SocialConfiguration;
use Celebron\social\State;
use Celebron\social\widgets\WidgetSupport;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;

/**
 *
 * @property-read mixed $data
 */
#[WidgetSupport(false, true)]
class Telegram extends AuthBase
{
    public string $clientSecret;

    public int $timeout = 86400;

    public function getData():array
    {
        $auth_data = \Yii::$app->request->get();
        $check_hash = $auth_data['hash'];
        unset($auth_data['hash'], $auth_data['social'], $auth_data['state']);
        $data_check_arr = [];
        foreach ($auth_data as $key => $value) {
            $data_check_arr[] = $key . '=' . $value;
        }

        sort($data_check_arr);
        $data_check_string = implode("\n", $data_check_arr);
        $secret_key = hash('sha256', $this->clientSecret, true);
        $hash = hash_hmac('sha256', $data_check_string, $secret_key);
        if (strcmp($hash, $check_hash) !== 0) {
            throw new BadRequestHttpException('Data is NOT from Telegram');
        }

        if ((time() - $auth_data['auth_date']) > $this->timeout) {
            throw new BadRequestHttpException('Data is outdated');
        }
        return $auth_data;
    }

    /**
     * @param string|null $code
     * @param State $state
     * @param SocialConfiguration $config
     * @return Response
     * @throws BadRequestHttpException
     */
    public function request(?string $code, State $state, SocialConfiguration $config): Response
    {
        return $this->response('id', $this->getData());
    }
}