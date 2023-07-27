<?php

use Celebron\socialSource\behaviors\ViewerBehavior;
use Celebron\socialSource\Configuration;
use Celebron\socialSource\interfaces\ViewerInterface;
use Celebron\socialSource\Request;
use Celebron\socialSource\widgets\SocialAsset;

/* @var \yii\web\View $this */
/* @var Request[]|ViewerInterface[]|ViewerBehavior[] $socials */
/* @var Configuration $configure */

SocialAsset::register($this);
?>


<div class="social-management-block">
    <?php foreach ($socials as $social): if ($social->getSupportLogin() && $social->visible): ?>
        <div class="social-management" id="social-<?= $social->socialName ?>">
            <?= $this->render('managementOne', ['configure' => $configure, 'social' => $social]) ?>
        </div>
    <?php endif; endforeach; ?>
</div>
