<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

namespace Celebron\source\social\behaviors;


use Celebron\source\social\Configuration;
use yii\helpers\ArrayHelper;

class Behavior extends \yii\base\Behavior
{
    protected array $params = [];

    public function __construct (
        protected readonly string        $socialName,
        protected readonly Configuration $configure,
        array                            $config = []
    )
    {
        parent::__construct($config);
        $this->params = ArrayHelper::getValue(\Yii::$app->params, [$this->configure->paramsGroup, $this->socialName], []);
    }
}