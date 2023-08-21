<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\socialSource;

use Celebron\socialSource\interfaces\SocialUserInterface;
use Celebron\socialSource\responses\Id;
use Closure;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\web\UnauthorizedHttpException;

trait UserManagementTrait
{
    abstract public function getRememberTime():int;
    abstract public function secure(Social $social, string $method);

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
    #[Secure('secure')]
    public function socialRegister (Id $response): Response
    {
        /** @var ActiveRecord&SocialUserInterface $this */
        return $response->saveModel($this);
    }

    /**
     * @throws \Exception
     */
    #[Secure('secure')]
    public function socialDelete (Social $social): Response
    {
        /** @var ActiveRecord&SocialUserInterface $this */
        return Response::saveModel($social, $this);
    }

}