<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social;

use Celebron\source\social\interfaces\SocialUserInterface;
use Celebron\source\social\responses\Id;
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
        if($response instanceof Social) {
            $name = $response->name;
        } else {
            $name = $response->social->name;
            $value = $response->getId();
        }
        $field = $model->getSocialField($name);
        $model->$field = $value;
        $result = new self($model->save());
        $result->response = $model;
        return $result;
    }
}