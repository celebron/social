<?php

namespace Celebron\socialSource\responses;

use Celebron\socialSource\Social;
use yii\helpers\ArrayHelper;

class IdResponse
{
    public function __construct (
        public readonly Social                      $social,
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