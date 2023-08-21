<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social\traits;

use Celebron\source\social\{responses\Id, responses\Response, Social};

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

    public function socialLogin(Id $response):Response
    {
        /** @var \yii\web\IdentityInterface&\Celebron\source\social\interfaces\SocialUserInterface $this */
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
        /** @var \yii\db\ActiveRecord&\Celebron\source\social\interfaces\SocialUserInterfac $this */
        return $response->saveModel($this);
    }

    /**
     * @throws \Exception
     */
    #[Secure('secure')]
    public function socialDelete (Social $social): Response
    {
        /** @var \yii\db\ActiveRecord&\Celebron\source\social\interfaces\SocialUserInterfac $this */
        return Response::saveModel($social, $this);
    }

}