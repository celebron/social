<?php


use Celebron\socialSource\behaviors\ViewerBehavior;
use Celebron\socialSource\Configuration;
use Celebron\socialSource\interfaces\ViewerInterface;
use Celebron\socialSource\Request;
use yii\helpers\Html;


/* @var \yii\web\View $this */
/* @var Request|ViewerInterface|ViewerBehavior $social */
/* @var Configuration $configure */

$statusPlay = Html::a("<i class='bi bi-play'></i>", $social->url('register'));
$statusStop = "<i class='bi bi-stop'></i>";
$statusRegistered = Html::a("<i class='bi bi-toggle2-on'></i>", $social->url('delete'));
$statusDeleted = Html::a("<i class='bi bi-toggle2-off'></i>", $social->url('register'));


?>
<div class="social-name"><?= $social->name ?></div>
<?php if (empty($social->getSocialId())): ?>
    <div class="social-id empty"><?= $statusStop ?></div>
    <div class="social-manage empty">
        <?= $statusDeleted ?>
    </div>
<?php else: ?>
    <div class="social-id isset">
        <?= $statusPlay ?>
        <?= $social->getSocialId() ?>
    </div>
    <div class="social-manage isset">
        <?= $statusRegistered ?>
    </div>
<?php endif; ?>
