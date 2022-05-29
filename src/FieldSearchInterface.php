<?php

namespace Celebron\social;

use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

interface FieldSearchInterface extends IdentityInterface
{
    public static function fieldSearch(string $field, mixed $id) : ?ActiveRecord;

    public function setAuthKey() : void;
}