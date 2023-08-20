<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

use Celebron\source\social\traits\ViewerBehavior;
use Celebron\source\social\Configuration;
use Celebron\source\social\interfaces\ViewerInterface;
use Celebron\source\social\Social;
use Celebron\widgets\social\SocialAsset;

/* @var \yii\web\View $this */
/* @var Social[]|ViewerInterface[] $socials */
/* @var Configuration $configure */
?>

<table class="table social-management-block">
    <?php foreach ($socials as $key=>$social): if ($social->getSupportLogin() && $social->getVisible()): ?>
        <tr class="social-management" id="social-<?= $key ?>">
            <?= $this->render('managementOne', ['configure' => $configure, 'social' => $social], $this->context) ?>
        </tr>
    <?php endif; endforeach; ?>
</table>
