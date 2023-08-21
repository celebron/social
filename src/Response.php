<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\socialSource;

use Celebron\socialSource\interfaces\SocialUserInterface;
use Celebron\socialSource\responses\Id;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class Response
{
    public mixed $response; //Передача в success или failed

    public function __construct (
        public bool $success,
        public readonly string $comment
    )
    {
    }

    /**
     * @throws \Exception
     */
    public static function saveModel (Id|Social $response, ActiveRecord&SocialUserInterface $model, mixed $value = null): self
    {
        if($response instanceof Id) {
            $value = $response->getId();
            $response = $response->social;
        }

        $field = $model->getSocialField($response->socialName);
        $model->$field = $value;
        $result = new self($model->save(), "Save $field to model " . $model::class);
        $result->response = $model;
        return $result;
    }
}