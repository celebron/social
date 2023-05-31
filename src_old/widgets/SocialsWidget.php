<?php

namespace Celebron\social\old\widgets;

use Celebron\social\old\interfaces\ToWidgetInterface;
use Celebron\social\old\Social;
use Celebron\social\old\SocialAsset;
use Celebron\social\old\SocialConfiguration;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * Виджет списка соцсетей
 */
class SocialsWidget extends Widget
{

    public string $type = SocialWidget::TYPE_LOGIN;

    public bool|string $icon = false;

    public string $loginText = "%s";

    public array $loginOptions = [];
    public array $iconOptions = [];
    public array $registerOptions = [];
    public array $options = [];

    /** @var Social[]  */
    private array $_socials = [];

    public function init ()
    {
        parent::init();
        $this->_socials = SocialConfiguration::socialsStatic(ToWidgetInterface::class);
    }

    /**
     * @throws \yii\base\InvalidConfigException|\Throwable
     */
    public function run()
    {
        $html = Html::beginTag('div',['class'=> 'socials-block']);
        foreach ($this->_socials as $social) {
            /** @var ToWidgetInterface $social  */
            if($this->type === SocialWidget::TYPE_REGISTER && !$social->getVisible()) {
                continue;
            }
            $html .= SocialWidget::widget([
                'options' => $this->options,
                'iconOptions' => $this->iconOptions,
                'registerOptions' => $this->registerOptions,
                'loginOptions' => $this->loginOptions,
                'type' => $this->type,
                'icon' => $this->icon,
                'loginText' => $this->loginText,
                'social' => ($social::class)::socialName()
            ]);
        }
        $html .= Html::endTag('div');
        return $html;
    }

}