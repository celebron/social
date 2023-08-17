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
/* @var Social[]|ViewerInterface[]|ViewerBehavior[] $socials */
/* @var Configuration $configure */

SocialAsset::register($this);
?>

<table class="table social-management-block">
    <?php foreach ($socials as $social): if ($social->getSupportLogin() && $social->visible): ?>
        <tr class="social-management" id="social-<?= $social->socialName ?>">
            <?= $this->render('managementOne', ['configure' => $configure, 'social' => $social], $this->context) ?>
        </tr>
    <?php endif; endforeach; ?>
</table>
