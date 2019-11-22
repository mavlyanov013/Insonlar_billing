<?php
/**
 * Created by PhpStorm.
 * User: shavkat
 * Date: 12/30/17
 * Time: 3:03 PM
 */
$session = \Yii::$app->session;
?>
<?php foreach ($messages as $type => $items): ?>
    <div class="alert alert-<?= $type ?> alert-dismissible fade show">
        <?php foreach ($items as $i => $message): ?>
            <?= $message ?>
        <?php endforeach; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
    </div>
    <?php $session->removeFlash($type); ?>
<?php endforeach; ?>
