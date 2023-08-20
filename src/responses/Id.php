<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social\responses;

use Celebron\source\social\Social;
use yii\helpers\ArrayHelper;

readonly class Id
{
    public function __construct (
        public Social                 $social,
        private string|\Closure|array $fieldFromSocial,
        public array|object           $data,
    ){
    }

    /**
     * @throws \Exception
     */
    public function getId():mixed
    {
        return ArrayHelper::getValue($this->data, $this->fieldFromSocial);
    }

}