<?php

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
        $field = $model->getSocialField($response->social->socialName);
        $model->$field = ($response instanceof Id) ? $response->getId() : $value;
        $result = new self($model->save());
        $result->response = $model;
        return $result;
    }
}