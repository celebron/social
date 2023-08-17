<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social\traits;

use Celebron\source\social\Response;
use Celebron\source\social\responses\Id;
use Celebron\source\social\Social;
use yii\web\UnauthorizedHttpException;

/**
 * @method getSocialField(string $socialName)
 */
trait UserManagementTrait
{
    abstract public static function fieldSearch (string $field, mixed $id): ?self;
    abstract public function getRememberTime():int;

    /**
     * @throws UnauthorizedHttpException
     * @throws \Exception
     */
    public function socialLogin(Id $response):bool
    {
        $field = $this->getSocialField($response->social->socialName);
        $login = $this::fieldSearch($field, $response->getId());
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