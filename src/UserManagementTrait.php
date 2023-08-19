<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\socialSource;

use Celebron\socialSource\responses\Id;
use yii\web\UnauthorizedHttpException;

trait UserManagementTrait
{
    abstract public static function fieldSearch (string $field, mixed $id): ?self;
    abstract public function getRememberTime():int;

    public function socialLogin(Id $response):bool
    {
        $field = $this->getSocialField($response->social->socialName);
        $login = static::fieldSearch($field, $response->getId());
        if ($login === null) {
            throw new UnauthorizedHttpException(\Yii::t('social', 'Not authorized'));
        }
        return \Yii::$app->user->login($login, $this->getRememberTime());
    }

    /**
     * @throws \Exception
     */
    public function socialRegister (Id $response): Response
    {
        return Response::saveModel($response, $this);
    }

    /**
     * @throws \Exception
     */
    public function socialDelete (Social $social): Response
    {
        return Response::saveModel($social, $this);
    }

}