<?php

namespace Celebron\social\old\eventArgs;

use yii\base\Event;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Параметры события FindUser
 * @property  ActiveQuery $userQuery - ActiveQuery от User класса
 */
class FindUserEventArgs extends Event
{
    /** @var ActiveRecord|null - Пользователь User */
    public ?ActiveRecord $user;

    /**
     * Конструктор
     * @param ActiveQuery $userQuery - ActiveQuery User
     * @param array $config - стандартный конфиг Yii2
     */
    public function __construct (public ActiveQuery $userQuery, array $config = [])
    {
        parent::__construct($config);
        //Выполнение Query запроса $userQuery
        $this->user = $this->userQuery->one();
    }

}