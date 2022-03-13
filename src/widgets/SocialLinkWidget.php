<?php

namespace Celebron\social\widgets;

use Celebron\social\SocialConfiguration;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class SocialLinkWidget extends \yii\base\Widget
{
    public string $groupClass = 'social-auth';

    public bool $iconEnable = true;

    public array $options = [];
    public array $linkOptions = [];
    public array $iconOptions = [];
    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function run()
    {
        $options = ArrayHelper::merge([
            'class' => $this->groupClass . '-group',
        ], $this->options);
        $html = Html::beginTag('div', $options) . PHP_EOL;
        foreach (SocialConfiguration::config()->getLinks() as  $k => $v){
            $linkOption = ArrayHelper::merge([
                'class' => 'social-' . $k,
            ], $this->linkOptions);

            $iconOptions = ArrayHelper::merge([
                'alt' => $v['name'],
            ],$this->iconOptions);

            $icon = $v['name'];
            if(!is_null($v['icon']) && $this->iconEnable) {
                $icon = Html::img($v['icon'], $iconOptions);
            }

            $html .= "\t" . Html::beginTag('div', [ 'class'=> $this->groupClass . ' ' . $k ]) . PHP_EOL;
            $html .= "\t\t" . Html::a($icon, $v['link'], $linkOption) . PHP_EOL;
            $html .= "\t" . Html::endTag('div') . PHP_EOL;
        }
        $html .= Html::endTag('div') . PHP_EOL;

        return $html;
    }

}