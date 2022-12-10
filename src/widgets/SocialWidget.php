<?php

namespace Celebron\social\widgets;

use Celebron\social\SocialAsset;
use Celebron\social\SocialConfiguration;
use Celebron\social\Social;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class SocialWidget extends Widget
{
    public const TYPE_LOGIN = 'login';
    public const TYPE_REGISTER = 'register';

    public string $social;
    public string $type = self::TYPE_LOGIN;

    public string $groupClass = 'social';

    public bool|string $icon = false;

    public string $loginText = "%s";
    public array $loginOptions = [
        'icon'=> [],
        'link' =>[],
    ];

    public array $iconOptions = [];

    public array $registerOptions = [

    ];

    private ?Social $_social = null;

    public function init ()
    {
        parent::init();
        SocialAsset::register($this->view);
        $this->_social = SocialConfiguration::socialStatic($this->social);
    }

    public function run ()
    {
        $html = '';
        if($this->_social !== null) {
            $html .= Html::beginTag('div', [
                    'class' => ["{$this->groupClass}-{$this->type}", "{$this->groupClass}-{$this->social}"]
                ]) . PHP_EOL;


            $html .= match ($this->type) {
                self::TYPE_LOGIN => $this->runLogin(),
                self::TYPE_REGISTER => $this->runRegister()
            };


            $html .= Html::endTag('div');
        }
        return $html;
    }

    public function runLogin(): string
    {
        $alt = sprintf($this->loginText, $this->_social->name);
        $text = $this->getIcon(true) ?? $alt;
        return "\t" . Html::a($text, ($this->_social::class)::url(false), $this->loginOptions['link']) . PHP_EOL;
    }

    public function getIcon(bool $html = false)
    {
        if (is_bool($this->icon)) {
            $icon = $this->icon && !empty($this->_social->icon) ? \Yii::getAlias($this->_social->icon) : null;
        } else {
            $icon = \Yii::getAlias($this->icon);
        }

        return ($html && !is_null($icon)) ? Html::img($icon, $this->iconOptions) : $icon;
    }

    /**
     * @return string
     */
    public function runRegister(): string
    {
        $regText = $this->_social->getSocialId();
        $deleteText = Html::a('Delete', ($this->_social::class)::url(null));

        if($regText === null) {
            $regText = "Регистрация";
            $deleteText = '';
        }

        $html = Html::tag('div', $this->getIcon(true) ?? $this->_social->name, [ 'class' => "{$this->groupClass}-name" ]);
        $html .= Html::beginTag('div', [ 'class' => "{$this->groupClass}-id" ]);
        $html .= Html::a($regText, ($this->_social::class)::url(true));
        $html .= Html::endTag('div');
        $html .= Html::tag('div', $deleteText);
        return $html;
    }
}