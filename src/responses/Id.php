<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\socialSource\responses;

use Celebron\socialSource\Social;
use yii\helpers\ArrayHelper;

class Id
{
   private mixed $_fieldIdFromSocial = null;


    public function __construct (
        public readonly Social $social,
        public array|object $data
    ){
    }

    /**
     * @throws \Exception
     */
    public function getId():mixed
    {
        return ArrayHelper::getValue($this->data, $this->_fieldIdFromSocial);
    }

    public function fieldToId(string|\Closure|array $value):self
    {
        $this->_fieldIdFromSocial = $value;
        return $this;
    }

}