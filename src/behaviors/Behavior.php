<?php

namespace Celebron\social\behaviors;

use Celebron\social\Configuration;


class Behavior extends \yii\base\Behavior
{
    protected array $params = [];

    public function __construct (
        protected readonly string                         $socialName,
        protected readonly \Celebron\social\Configuration $configure,
        array                                             $config = []
    )
    {
        parent::__construct($config);
        if (isset($this->configure->paramsGroup, \Yii::$app->params[$this->configure->paramsGroup][$this->socialName])) {
            $this->params = \Yii::$app->params[$this->configure->paramsGroup][$this->socialName];
        }
    }
}