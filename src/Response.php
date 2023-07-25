<?php

namespace Celebron\social;

use Celebron\social\interfaces\SocialInterface;
use yii\base\Model;
use yii\db\ActiveRecord;

class Response
{
    public mixed $response = null;

    public function __construct (
        public readonly bool $success
    )
    {
    }

    /**
     * @throws \Exception
     */
    public static function saveModel(SocialResponse|SocialAuthBase $response, ActiveRecord&SocialInterface $model, mixed $value = null):self
    {
        $field = $model->getSocialField($response->socialName);
        $model->$field = ($response instanceof SocialResponse) ? $response->getId() : $value;
        $result = new self($model->save());
        $result->response = $model;
        return $result;
    }

}