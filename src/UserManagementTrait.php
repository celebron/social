<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\socialSource;

use Celebron\socialSource\interfaces\SocialUserInterface;
use Celebron\socialSource\responses\Id;
use Closure;
use yii\web\UnauthorizedHttpException;

trait UserManagementTrait
{
    abstract public function getRememberTime():int;
    abstract public function secure();

    public function socialLogin(Id $response):bool
    {
        /** @var SocialUserInterface  $this */
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