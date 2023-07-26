<?php

namespace Celebron\socialSource;

use yii\helpers\ArrayHelper;

class ResponseSocial
{
    public function __construct (
        public readonly string                      $socialName,
        private readonly string|\Closure|array|null $field,
        public readonly mixed                       $data,
    ){
    }

    /**
     * @throws \Exception
     */
    public function getId():mixed
    {
        return ArrayHelper::getValue($this->data, $this->field);
    }

}