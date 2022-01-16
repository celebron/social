<?php

namespace Celebron\social;

use yii\db\ActiveRecord;

interface FieldSearchInterface
{
    public static function fieldSearch(string $field, mixed $id) : ?ActiveRecord;
}