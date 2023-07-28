<?php

use Celebron\socialSource\behaviors\ViewerBehavior;
use Celebron\socialSource\Configuration;
use Celebron\socialSource\interfaces\ViewerInterface;
use Celebron\socialSource\Social;
use Celebron\socialSource\widgets\SocialAsset;

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
