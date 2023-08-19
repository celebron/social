<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\socialSource;

use Celebron\socialSource\interfaces\SocialUserInterface;
use Celebron\socialSource\responses\Id;
use yii\db\ActiveRecord;

class Response
{
    public mixed $response; //Передача в success или failed

    public function __construct (
        public bool $success
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
        $result = new self($model->save());
        $result->response = $model;
        return $result;
    }
}