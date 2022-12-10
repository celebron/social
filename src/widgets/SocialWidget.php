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

    public bool|string $loginIcon = false;
    public string $loginText = "%s";
    public array $loginOptions = [
        'icon'=> [],
        'link' =>[],
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

    public function runLogin()
    {
        $icon = is_bool($this->loginIcon) ? ( $this->loginIcon ? $this->_social->icon : null ) : $this->loginIcon;
        $alt = sprintf($this->loginText, $this->_social->name);
        $text = is_null($icon) ? $alt : Html::img(\Yii::getAlias($icon),['alt'=> $alt], $this->loginOptions['icon']);
        return "\t" . Html::a($text, ($this->_social::class)::url(false), $this->loginOptions['link']) . PHP_EOL;
    }

    /**
    <div class="account-social-google">
    <div class="social-name">Google</div>
    <div class="social-id"><?= $user->id_google ?? Yii::t('app','Не заданно') ?></div>
    <div class="links">
    <?= Google::a("Изменить", true,['error'=> true]) ?>
    <?php if($user->id_google !== null):?>
    <a href="<?= Url::to(['account/social-delete','state'=>'google']) ?>" class="link-delete">Отсоединить</a>
    <?php endif; ?>
    </div>
    </div>
     */
    /**
     * @return void
     */
    public function runRegister()
    {
        $html = Html::tag('div', $this->_social->name, [ 'class' => "{$this->groupClass}-name" ]);
        $html .= Html::beginTag('div', [ 'class' => "{$this->groupClass}-id" ]);

            $html .= Html::a();
        $html .= Html::endTag('div');
        return $html;
    }
}