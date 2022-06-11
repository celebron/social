<?php

namespace Celebron\social;

use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Интерфейс приска
 */
interface FieldSearchInterface extends IdentityInterface
{
    /**
     * поиск пользователя в базе данных по полю и иду
     * @param string $field
     * @param mixed $id
     * @return ActiveRecord|null
     */
    public static function fieldSearch(string $field, mixed $id) : ?ActiveRecord;
}