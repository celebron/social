<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace Celebron\social;

use yii\web\AssetBundle;

/**
 * Twitter Bootstrap 5 CSS bundle.
 */
class BootstrapAsset extends AssetBundle
{
    /**
     * @inheritDoc
     */
    public $sourcePath = '@bower/bootstrap';

    /**
     * @inheritDoc
     */
    public $css = [
        'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.0/font/bootstrap-icons.css',
        'dist/css/bootstrap.css'
    ];
}
