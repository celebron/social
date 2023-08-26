<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social\responses;

use Celebron\source\social\interfaces\SocialUserInterface;
use Celebron\source\social\Social;
use yii\db\ActiveRecord;

class Response
{
    public mixed $response; //Передача в success или failed

    public readonly string $comment;
    public function __construct (
        public bool $success,
        string $comment,
        array $commentParams = [],
    )
    {
        $placeholders = [];
        $commentParams['success'] = $this->success;
        $commentParams['successText'] = $this->success ? 'successful': 'failed';
        foreach ($commentParams as $name => $value) {
            $placeholders['{' . $name . '}'] = $value;
        }

        $this->comment = ($placeholders === []) ? $comment : strtr($comment, $placeholders);
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

        $field = $model->getSocialField($response->name);
        $model->$field = $value;
        $result = new self($model->save(), 'Save field "{field}" to model "{model}" - {successText}',[
            'field' => $field,
            'model' => $model::class,
        ]);
        $result->response = $model;
        return $result;
    }
}