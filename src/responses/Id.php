<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\socialSource\responses;

use Celebron\socialSource\Social;
use yii\helpers\ArrayHelper;

class Id
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