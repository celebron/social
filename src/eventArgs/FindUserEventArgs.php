<?php

namespace Celebron\social\eventArgs;

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
    public ?ActiveRecord $user = null;

    /**
     * Конструктор
     * @param ActiveQuery $userQuery - ActiveQuery User
     * @param array $config - стандартный конфиг Yii2
     */
    public function __construct (public ActiveQuery $userQuery, array $config = [])
    {
        parent::__construct($config);
    }

    /**
     * Алгоритм поиска по-умолчанию
     * @return void
     */
    public function defaultAlg(): void
    {
        if($this->sender->id !== null) {
            $this->user = $this->userQuery->andWhere([$this->sender->field => $this->sender->id])->one();
        }
    }
}