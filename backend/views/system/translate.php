<?php

use common\components\Config;
use yii\widgets\ActiveForm;

$len = strlen($model->message);
?>

<div class="row">
    <?php
    $form = ActiveForm::begin([
                                  'action' => Yii::$app->urlManager->createUrl(['/system/translate', 'id' => $model->id]),
                                  'id'     => 'translation-form']);
    ?>
    <div class="col col-md-12">
        <div class="form-group">
            <label class="control-label" for="translation-message"><?= __('Message') ?></label>

            <p id="translation-message"><?= $model->message ?></p>
            <hr>
        </div>
        <?php foreach (Config::getLanguageOptions() as $language => $value): ?>
            <div class="form-group">
                <label class="control-label"
                       for="translation-<?= $language ?>"><?= $value ?></label>
                <?php if ($len > 100): ?>
                    <textarea class="form-control" rows="4"
                              id="translation-<?= $language ?>"
                              name="SystemMessage[<?= $language ?>]"><?= $model->getAttribute($language) ?></textarea>
                <?php else: ?>
                    <input type="text"
                           class="form-control"
                           id="translation-<?= $language ?>"
                           name="SystemMessage[<?= $language ?>]"
                           value="<?= $model->getAttribute($language) ?>">
                <?php endif; ?>
            </div>
        <?php endforeach ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>