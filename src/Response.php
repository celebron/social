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
    public static function saveModel(SocialResponse $response, ActiveRecord&SocialInterface $model, bool $delete):self
    {
        $field = $model->getSocialField($response->social);
        $model->$field = $delete ? null : $response->getId();
        $result = new self($model->save());
        $result->response = $model;
        return $result;
    }

}