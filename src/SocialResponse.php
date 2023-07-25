<?php

namespace Celebron\social;

use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

/**
 *
 * @property-read mixed $id
 */
class SocialResponse extends BaseObject
{
    public function __construct (
        public readonly string                      $socialName,
        private readonly string|\Closure|array|null $field,
        public readonly mixed                       $data,
        array                                       $config = []
    ){
        parent::__construct($config);
    }

    /**
     * @throws \Exception
     */
    public function getId():mixed
    {
        return ArrayHelper::getValue($this->data, $this->field);
    }

}