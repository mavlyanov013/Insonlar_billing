<?php
use \yii\widgets\ActiveForm;
use yii\helpers\Html;
?>
<div class="region-create">

    <div class="admin-form">
        <?php $form = ActiveForm::begin([
            'enableAjaxValidation' => true,
        ]); ?>
        <div class="row">
            <div class="col col-md-3"></div>
            <div class="col col-md-6">
                <div class="panel">
                    <div class="panel-heading border">
                        <h3><?= __('Category Information') ?></h3>
                    </div>
                    <div class="panel-body">
                        <?= $form->field($model, 'name')->textInput(['maxlength' => true,])->label() ?>
                        <?= $form->field($model, 'slug')->textInput(['maxlength' => true,])->label() ?>
                        <?= $form->field($model, 'parent_id')->dropDownList(\yii\helpers\ArrayHelper::map(
                            \common\models\Category::find()->where(['not in', 'id', [$model->id]])->all(),
                            'id',
                            'name'
                        ), ['prompt' => __("Parent category")])?>
                    </div>
                    <div class="panel-footer ">
                        <?= Html::submitButton(__('Save'), ['class' => 'btn btn-primary']) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col col-md-3"></div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
