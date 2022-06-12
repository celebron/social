<?php

namespace Celebron\social\eventArgs;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class FindUserEventArgs extends \yii\base\Event
{
    public ?ActiveRecord $user = null;

    public function __construct (public ActiveQuery $userQuery, $config = [])
    {
        parent::__construct($config);
    }
}