<?php
/*
 * Copyright (c) 2023.  Aleksey Shatalin (celebron) <celebron.ru@yandex.ru>
 */

use Celebron\source\social\behaviors\ViewerBehavior;
use Celebron\source\social\Configuration;
use Celebron\source\social\interfaces\ViewerInterface;
use Celebron\source\social\Social;
use Celebron\widgets\social\SocialAsset;
use yii\helpers\Html;
use yii\web\View;

/* @var View $this */
/* @var Social[]|ViewerInterface[]|ViewerBehavior[] $socials */
/* @var Configuration $configure */

?>

<div class="social-login-block">
    <?php foreach ($socials as $key => $social): if ($social->getSupportLogin() && $social->visible): ?>
        <div class="social-login" id="social-$key">
            <a href="<?= $social->url(ViewerInterface::VIEW_LOGIN) ?>">
                <?= $this->context->useIcon
                    ? Html::img($social->icon, ['alt'=>$social->name])
                    : $social->name
                ?>
            </a>
        </div>
    <?php endif; endforeach; ?>
</div>
