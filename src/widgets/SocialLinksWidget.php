<?php

namespace Celebron\social\widgets;

use Celebron\social\SocialAsset;
use Celebron\social\SocialConfiguration;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Виджет списка соцсетей
 */
class SocialLinksWidget extends Widget
{
    public string $groupClass = 'social-auth';

    public bool $iconEnable = true;
    public bool $register = false;

    public array $options = [];
    public array $linkOptions = [];
    public array $iconOptions = [];

    public function init ()
    {
        parent::init();
        SocialAsset::register($this->view);
    }



    /**
     * <div class='social-auth-begin'>
     *  <div class='social-auth social-vk'>
     *  </div>
     *  <div class='social-auth social-google'>
     *  </div>
     * </div>
     */
    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function run()
    {

        return '';
    }

}