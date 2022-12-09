<?php

namespace Celebron\social\widgets;

use Celebron\social\SocialAsset;
use Celebron\social\SocialConfiguration;
use yii\base\Widget;

class SocialViewWidget extends Widget
{
    public const TYPE_LOGIN = 'login';
    public const TYPE_REGISTER = 'register';

    public string $social;
    public string $type = self::TYPE_LOGIN;

    private $_data;

    public function init ()
    {
        parent::init();
        SocialAsset::register($this->view);
        $this->_data = SocialConfiguration::linksStatic();
    }

    public function run ()
    {
        return print_r($this->_data, true);
    }

}