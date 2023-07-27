<?php

namespace Celebron\socialSource\behaviors;


use Celebron\socialSource\Configuration;
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