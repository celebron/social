<?php

namespace Celebron\social\socials;

use Celebron\social\AuthBase;
use Celebron\social\interfaces\AuthActionInterface;
use Celebron\social\SocialConfiguration;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;

/**
 *
 * @property-read mixed $data
 */
class Telegram extends AuthBase implements AuthActionInterface
{
    public string $clientSecret;
    public int $id;
    public int $timeout = 86400;

    final public function actionLogin(SocialConfiguration $config) : bool
    {
        $data = $this->getData();
        $this->id = $data['id'];
        if(($user = $this->findUser($data['id'])) !== null) {
            $login = \Yii::$app->user->login($user, $config->duration);
            \Yii::debug("User login ({$data['id']}) " . $login ? "succeeded": "failed", static::class);
            return $login;
        }
        return false;
    }

    public function getData()
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

    final public function actionRegister(SocialConfiguration $config) : bool
    {
        $data = $this->getData();
        \Yii::debug("Register social '" . static::socialName() ."' to user");
        return $this->modifiedUser($data['id']);
    }

    /**
     * Удаление записи соц УЗ.
     * @return bool
     * @throws InvalidConfigException
     */
    final public function actionDelete(SocialConfiguration $config) : bool
    {
        \Yii::debug("Delete social '" . static::socialName() . "' to user");
        return $this->modifiedUser(null);
    }
}