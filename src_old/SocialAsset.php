<?php

namespace Celebron\src_old;

use yii\web\AssetBundle;
use yii\web\JqueryAsset;

class SocialAsset extends AssetBundle
{
    public $sourcePath = '@Celebron/social/public';
    public $js = ['social.js'];
    public $css = ['social.css'];
    public $depends = [
        JqueryAsset::class,
        BootstrapAsset::class,
    ];
}