<?php

namespace Celebron\socials;


use Celebron\socialSource\interfaces\ViewerInterface;
use Celebron\socialSource\Social;
use Celebron\socialSource\ResponseSocial;
use Celebron\socialSource\State;
use yii\web\BadRequestHttpException;

/**
 *
 * @property-read mixed $data
 */
class Telegram extends Social
{
    public string $clientSecret;

    public int $timeout = 86400;

    /**
     * @throws BadRequestHttpException
     */
    public function getData (): array
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
     * @throws BadRequestHttpException
     */
    public function request(?string $code, State $state): ResponseSocial
    {
        return $this->response('id', $this->getData());
    }

}