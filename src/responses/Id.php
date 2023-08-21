<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social\responses;

use Celebron\source\social\Social;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;
use yii\web\UnauthorizedHttpException;

readonly class Id
{
    public function __construct (
        public Social                 $social,
        private string|\Closure|array $fieldFromSocial,
        public array|object           $data,
    ){
    }

    /**
     * @throws \Exception
     */
    public function getId():mixed
    {
        return ArrayHelper::getValue($this->data, $this->fieldFromSocial);
    }

    public function login(IdentityInterface&SocialUserInterface $thisObject, $rememberTime):Response
    {
        $field = $thisObject->getSocialField($this->social->socialName);
        /** @var IdentityInterface&SocialUserInterface $identity */
        $identity = $thisObject::fieldSearch($field, $this->getId());
        if ($identity === null) {
            throw new UnauthorizedHttpException(\Yii::t('social', 'Not authorized'));
        }
        $login = \Yii::$app->user->login($identity, $rememberTime);
        $result = new Response($login, "User from social '$this->social' authorized {successText}");
        $result->response = $identity;
        return $result;
    }

    /**
     * @throws \Exception
     */
    public function saveModel(ActiveRecord&SocialUserInterface $model): Response
    {
        return Response::saveModel($this, $model);
    }
}