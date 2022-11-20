<?php

namespace Celebron\social;

use yii\web\AssetBundle;
use yii\web\JqueryAsset;

class SocialAsset extends AssetBundle
{
    public $sourcePath = 'public';
    public $js = ['social.js'];
    public $css = ['social.css'];
    public $depends = [
        JqueryAsset::class,
    ];
}