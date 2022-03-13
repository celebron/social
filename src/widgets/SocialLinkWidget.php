<?php

namespace Celebron\social\widgets;

use Celebron\social\SocialConfiguration;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class SocialLinkWidget extends \yii\base\Widget
{
    public string $groupClass = 'social-auth';
    public array $linkOptions = [];
    public bool $iconEnable = true;
    public array $iconOptions = [];

    public array $options = [];

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function run()
    {
        $options = ArrayHelper::merge([
            'class' => $this->groupClass,
        ], $this->options);
        $html = Html::beginTag('div', $options) . PHP_EOL;
        foreach (SocialConfiguration::config()->getLinks() as  $k => $v){
            $linkOption = ArrayHelper::merge([
                'class' => 'social-' . $k,
            ], $this->linkOptions);

            $icon = $v['name'];
            if(!is_null($v['icon'] && $this->iconEnable)) {
                $icon = Html::img($v['icon'], $this->iconOptions);
            }

            $html .= "\t" . Html::beginTag('div', [ 'class'=> $this->groupClass . '-' . $k ]) . PHP_EOL;
            $html .= "\t\t" . Html::a($icon, $v['link'], $linkOption) . PHP_EOL;
            $html .= "\t" . Html::endTag('div') . PHP_EOL;
        }
        $html .= Html::endTag('div') . PHP_EOL;

        return $html;
    }

}