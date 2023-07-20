<?php

namespace Celebron\social;

use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

/**
 *
 * @property-read mixed $id
 */
class Response extends BaseObject
{
    //### Передача в success или failed ###//
    public mixed $response = null;

    public function __construct (
        public readonly string                      $social,
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

    public function isRequested():bool
    {
        return $this->field !== null && $this->data !== null;
    }

}