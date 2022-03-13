<?php

namespace Celebron\social\widgets;

use Celebron\social\SocialConfiguration;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class SocialLinkWidget extends \yii\base\Widget
{
    public string $groupClass = 'social-auth';
    public array $linkOptions = [];
    public array $options = [];

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function run()
    {
        $options = ArrayHelper::merge([
            'class' => $this->groupClass,
        ], $this->options);
        $html = Html::beginTag('div', $options) . "\n";
        foreach (SocialConfiguration::config()->getLinks() as  $k => $v){
            $linkOption = ArrayHelper::merge([
                'class' => 'social-' . $k,
            ], $this->linkOptions);
            $html .= "\t" . Html::a($v['name'], $v['link'], $linkOption) . "\n";
        }
        $html .= Html::endTag('div') . "\n";

        return $html;
    }

}