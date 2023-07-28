<?php

namespace Celebron\socialSource;

use Celebron\socialSource\interfaces\SocialUserInterface;
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
    public static function saveModel (ResponseSocial|Social $response, ActiveRecord&SocialUserInterface $model, mixed $value = null): self
    {
        $field = $model->getSocialField($response->socialName);
        $model->$field = ($response instanceof ResponseSocial) ? $response->getId() : $value;
        $result = new self($model->save());
        $result->response = $model;
        return $result;
    }
}