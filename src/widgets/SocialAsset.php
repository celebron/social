<?php

namespace Celebron\social\widgets;

use yii\web\AssetBundle;
use yii\web\JqueryAsset;

class SocialAsset extends AssetBundle
{
    public $sourcePath = '@Celebron/social/widgets/public';
    public $js = ['social.js'];
    public $css = ['social.css'];
    public $depends = [
        JqueryAsset::class,
    ];
}