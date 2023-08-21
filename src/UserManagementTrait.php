<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\socialSource;

use Celebron\socialSource\interfaces\SocialUserInterface;
use Celebron\socialSource\responses\Id;
use Closure;
use yii\web\IdentityInterface;
use yii\web\UnauthorizedHttpException;

trait UserManagementTrait
{
    abstract public function getRememberTime():int;
    abstract public function secure();

    /**
     * @throws UnauthorizedHttpException
     */
    public function socialLogin(Id $response):Response
    {
        /** @var IdentityInterface&SocialUserInterface $this */
        return $response->login($this, $this->getRememberTime());
    }

    /**
     * @throws \Exception
     */
    public function socialRegister (Id $response): Response
    {
        return $response->saveModel($this);
    }

    /**
     * @throws \Exception
     */

    public function socialDelete (Social $social): Response
    {
        return Response::saveModel($social, $this);
    }

}