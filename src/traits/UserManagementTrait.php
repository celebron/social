<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social\traits;

use Celebron\source\social\responses\Id;
use Celebron\source\social\responses\Response;
use Celebron\source\social\Social;

/**
 * @method getSocialField(string $socialName)
 */
trait UserManagementTrait
{
    abstract public function getRememberTime():int;

    /**
     * @param Social $social
     * @param string $method
     * @return bool
     */
    abstract public function secure(Social $social, string $method):bool;

    /**
     * @throws UnauthorizedHttpException
     */
    public function socialLogin(Id $response):Response
    {
        /** @var IdentityInterface&SocialUserInterface $this */
        return $response->login($this, $this->getRememberTime());
    }

    /**
     * @param Id $response
     * @return Response
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