<?php
/**
 *
 */
use common\components\Config;

?>
<?php if (Config::isLatinCyrill()): ?>
    <?php if (Yii::$app->language == Config::LANGUAGE_UZBEK): ?>
        <div class="form-group">
            <a class="btn btn-default" href="<?= $link ?>"
               onclick="return confirm('<?= htmlentities(__('Are you sure convert to CYRILLIC')) ?>')"
               style="width: 100%">
                <i class="fa fa-refresh"></i>&nbsp; <?= __('Convert to cyrillic') ?>
            </a>
        </div>
    <?php endif; ?>

    <?php if (Yii::$app->language == Config::LANGUAGE_CYRILLIC): ?>
        <div class="form-group">
            <a class="btn btn-default" href="<?= $link ?>"
               onclick="return confirm('<?= htmlentities(__('Are you sure convert to LATIN')) ?>')"
               style="width: 100%">
                <i class="fa fa-refresh"></i>&nbsp; <?= __('Convert to latin') ?>
            </a>
        </div>
    <?php endif; ?>
<?php endif; ?>
