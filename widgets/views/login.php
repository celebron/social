<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

use Celebron\source\social\traits\ViewerBehavior;
use Celebron\source\social\Configuration;
use Celebron\source\social\interfaces\ViewerInterface;
use Celebron\source\social\Social;
use yii\helpers\Html;
use yii\web\View;

/* @var View $this */
/* @var Social[]|ViewerInterface[] $socials */
/* @var Configuration $configure */

?>

<div class="social-login-block">
    <?php foreach ($socials as $key => $social): if ($social->getSupportLogin() && $social->getVisible()): ?>
        <div class="social-login" id="social-$key">

        </div>
    <?php endif; endforeach; ?>
</div>
