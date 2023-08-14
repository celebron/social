<?php

use Celebron\socialSource\Configuration;
use Celebron\socialSource\interfaces\ViewerInterface;
use Celebron\socialSource\Social;
use yii\helpers\ArrayHelper;

/* @var \yii\web\View $this */
/* @var Social[]|ViewerInterface[]|\Celebron\socialSource\behaviors\ViewerBehavior[] $socials */
/* @var Configuration $configure */


?>

<div class="social-login-block">
    <?php foreach ($socials as $key => $social): if ($social->getSupportLogin() && $social->visible): ?>
        <div class="social-login" id="social-$key">
            <a href="<?= $social->url(ViewerInterface::VIEW_LOGIN) ?>">
                <?= ucfirst($social->name) ?>
            </a>
        </div>
    <?php endif; endforeach; ?>
</div>
