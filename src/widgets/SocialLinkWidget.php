<?php

namespace Celebron\social\widgets;


use Celebron\social\SocialBase;
use yii\helpers\Html;

class SocialLinkWidget extends \yii\base\Widget
{
    public bool $register = false;

    public string $translateCategory = 'app';

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function run ()
    {
        $html = Html::beginTag('div',['class'=>"f"]) . "\n";
        foreach (SocialBase::config()->getLinks($this->register) as $key => $link) {
            $key = \Yii::t($this->translateCategory, $key);
            $html .= "\t" . Html::a($key, $link,[ 'class'=>'social-' . $key]) . "\n";
        }
        $html .= Html::endTag('div') . "\n";

        return $html;
    }
}