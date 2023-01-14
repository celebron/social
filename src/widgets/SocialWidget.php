<?php

namespace Celebron\social\widgets;

use Celebron\social\interfaces\ToWidgetInterface;
use Celebron\social\interfaces\ToWidgetLoginInterface;
use Celebron\social\interfaces\ToWidgetRegisterInterface;
use Celebron\social\SocialAsset;
use Celebron\social\SocialConfiguration;
use Celebron\social\Social;
use yii\base\NotSupportedException;
use yii\base\Widget;
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

    /**
     * @throws NotSupportedException
     */
    public function run ()
    {
        $html = '';
        if ($this->_social instanceof ToWidgetInterface) {
            $this->options['class'][] = "social-{$this->type}-block";
            $this->options['class'][] = "social_{$this->social}";
            $html .= Html::beginTag('div', $this->options) . PHP_EOL;
            if(($this->type === self::TYPE_LOGIN) && ($this->_social instanceof ToWidgetLoginInterface)) {
                $html .= $this->runLogin();
            } elseif(($this->type === self::TYPE_REGISTER) && ($this->_social instanceof ToWidgetRegisterInterface)) {
                $html .= $this->_social->getVisible() ? $this->runRegister() : $this->options['error'] ?? '';
            }
            $html .= Html::endTag('div') . PHP_EOL;
        }

        return $html;
    }

    /**
     * @return string
     */
    public function runLogin(): string
    {
        $alt = sprintf($this->loginText, $this->getName());
        $text = $this->getIcon(true) ?? $alt;
        return "\t" . Html::a($text, ($this->_social::class)::url(false), $this->loginOptions) . PHP_EOL;
    }

    public function getName() : string
    {
        return $this->_social->getName() ?? ($this->_social::class)::socialName();
    }

    /**
     * Иконка (ссылка или html)
     * @param bool $html - в виде html (true)
     * @return bool|string|null
     */
    public function getIcon(bool $html = false): bool|string|null
    {
        if (is_bool($this->icon)) {
            $icon = $this->icon && !empty($this->_social->getIcon()) ? \Yii::getAlias($this->_social->getIcon()) : null;
        } else {
            $icon = \Yii::getAlias($this->icon);
        }
        $this->iconOptions['alt'] = $this->iconOptions['alt'] ?? $this->getName();
        $this->iconOptions['class'][] = 'social-icon';
        return ($html && !is_null($icon)) ? Html::img($icon, $this->iconOptions) : $icon;
    }

    /**
     * @return string
     * @throws NotSupportedException
     */
    public function runRegister(): string
    {
        $socialId = $this->_social->getSocialId();
        $idText = Html::a("<i class='bi bi-play'></i>",($this->_social::class)::url(true));
        $idText .= $socialId;
        $toolText = Html::a("<i class='bi bi-toggle2-on'></i>", ($this->_social::class)::url(null));
        if($socialId === null) {
            $idText = "<i class='bi bi-stop'></i>";
            $toolText = Html::a("<i class='bi bi-toggle2-off'></i>",($this->_social::class)::url(true));
        }

        $this->registerOptions['icon']['class'][] = 'social-icon-view';
        $this->registerOptions['id']['class'][] = 'social-id-view';
        $this->registerOptions['tool']['class'][] = 'social-tool-view';
        $html = Html::tag('div', $this->getIcon(true) ?? $this->getName(), $this->registerOptions['icon']);
        $html .= Html::tag('div', $idText ,$this->registerOptions['id']);
        $html .= Html::tag('div', $toolText, $this->registerOptions['tool']);
        return $html;
    }
}