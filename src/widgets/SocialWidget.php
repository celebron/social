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

    public bool|string $icon = false;

    public string $loginText = "%s";

    public array $loginOptions = [];
    public array $iconOptions = [];
    public array $registerOptions = [];
    public array $options = [];

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
            $this->options['class'][] = "social-{$this->type}-block";
            $this->options['class'][] =  "social_{$this->social}";
            $html .= Html::beginTag('div', $this->options) . PHP_EOL;

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
        return "\t" . Html::a($text, ($this->_social::class)::url(false), $this->loginOptions) . PHP_EOL;
    }

    public function getIcon(bool $html = false)
    {
        if (is_bool($this->icon)) {
            $icon = $this->icon && !empty($this->_social->icon) ? \Yii::getAlias($this->_social->icon) : null;
        } else {
            $icon = \Yii::getAlias($this->icon);
        }
        $this->iconOptions['alt'] = $this->iconOptions['alt'] ?? $this->_social->name;
        $this->iconOptions['class'][] = 'social-icon';
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
        $this->registerOptions['icon']['class'][] = 'social-icon-view';
        $this->registerOptions['id']['class'][] = 'social-id-view';
        $this->registerOptions['delete']['class'][] = 'social-delete-view';
        $html = Html::tag('div', $this->getIcon(true) ?? $this->_social->name, $this->registerOptions['icon']);
        $html .= Html::beginTag('div', $this->registerOptions['id']);
        $html .= $this->runLogin($regText, true);
        $html .= Html::a($regText, ($this->_social::class)::url(true), $this->loginOptions);
        $html .= Html::endTag('div');
        $html .= Html::tag('div', $deleteText, $this->registerOptions['delete']);
        return $html;
    }
}