<?php

namespace Celebron\social\widgets;

use Celebron\social\interfaces\ToWidgetInterface;
use Celebron\social\SocialAsset;
use Celebron\social\SocialConfiguration;
use Celebron\social\Social;
use Celebron\social\WidgetSupport;
use yii\base\NotSupportedException;
use yii\base\Widget;
use yii\helpers\Html;
use yii\web\NotFoundHttpException;

/**
 *
 * @property-read string $name
 * @property Social&ToWidgetInterface $_social
 */
class SocialWidget extends Widget
{
    public const TYPE_LOGIN = 'login';
    public const TYPE_REGISTER = 'register';

    public string $social;
    public string $type = self::TYPE_LOGIN;
    public ?bool $visible = null; //null -> $_social->visible

    public bool|string $icon = false;

    public string $loginText = "%s";

    public array $loginOptions = [];
    public array $iconOptions = [];
    public array $registerOptions = [];
    public array $options = [];

    private null|Social|ToWidgetInterface $_social = null;
    private bool $_supportLogin = false;
    private bool $_supportRegister = false;

    /**
     * @throws \ReflectionException
     * @throws NotFoundHttpException
     */
    public function init ()
    {
        parent::init();
        SocialAsset::register($this->view);
        $this->_social = SocialConfiguration::socialStatic($this->social);
        $classRef = new \ReflectionClass($this->_social);
        $attributes = $classRef->getAttributes(WidgetSupport::class);
        if (isset($attributes[0])) {
            /** @var WidgetSupport $supports */
            $supports = $attributes[0]->newInstance();
            $this->_supportLogin = $supports->login;
            $this->_supportRegister = $supports->register;
        }
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
            if(($this->type === self::TYPE_LOGIN) && $this->_supportLogin) {
                $html .= $this->getVisible() ? $this->runLogin() : $this->options['loginNoVisible'] ?? '';
            } elseif(($this->type === self::TYPE_REGISTER) && $this->_supportRegister) {
                $html .= $this->getVisible() ? $this->runRegister() : $this->options['registerNoVisible'] ?? '';
            }
            $html .= Html::endTag('div') . PHP_EOL;
        }

        return $html;
    }

    public function getVisible():bool
    {
        return $this->visible ?? $this->_social->visible;
    }

    /**
     * @return string
     */
    public function runLogin(): string
    {
        /** @var Social $social */
        $social = $this->_social::class;
        $alt = sprintf($this->loginText, $this->getName());
        $text = $this->getIcon(true) ?? $alt;
        return "\t" . Html::a($text, $social::urlLogin(), $this->loginOptions) . PHP_EOL;
    }

    public function getName() : string
    {
        return  $this->_social->getName() ?? ($this->_social::class)::socialName();
    }

    /**
     * Иконка (ссылка или html)
     * @param bool $html - в виде html (true)
     * @return bool|string|null
     */
    public function getIcon(bool $html = false): bool|string|null
    {
        /** @var ToWidgetInterface $social */
        $social = $this->_social::class;

        if (is_bool($this->icon)) {
            $icon = $this->icon && !empty( $social->getIcon()) ? \Yii::getAlias( $social->getIcon()) : null;
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
        /** @var Social $social */
        $social = $this->_social::class;

        $socialId = $this->_social->getSocialId();
        $idText = Html::a("<i class='bi bi-play'></i>",$social::urlRegister());
        $idText .= $socialId;
        $toolText = Html::a("<i class='bi bi-toggle2-on'></i>", $social::urlDelete());
        if($socialId === null) {
            $idText = "<i class='bi bi-stop'></i>";
            $toolText = Html::a("<i class='bi bi-toggle2-off'></i>",$social::urlRegister());
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