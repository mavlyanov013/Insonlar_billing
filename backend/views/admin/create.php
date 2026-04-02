<?php
use common\components\Config;
use common\models\Admin;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii2mod\chosen\ChosenSelect;

/* @var $this yii\web\View */
/* @var $model common\models\Admin */

$this->title                   = __('Create Administrator');
$this->params['breadcrumbs'][] = ['url' => ['/backend/system/translation'], 'label' => __('System')];
$this->params['breadcrumbs'][] = ['url' => ['/backend/admin/index'], 'label' => __('Manage Administrators')];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="row">
    <div class="col col-md-8">
        <div class="panel">
            <?php $form = ActiveForm::begin(['validateOnSubmit' => true,]); ?>
            <div class="panel-heading border ">
                <h4><?= __('Account Information') ?></h4>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col col-md-7">
                        <?= $form->field($model, 'login')->textInput(['maxlength' => true,])->label() ?>
                    </div>
                    <div class="col col-md-5">
                        <?= $form->field($model, 'status')->widget(ChosenSelect::className(), [
                            'items'         => Admin::getStatusOptions(),
                            'pluginOptions' => ['width' => '100%', 'allow_single_deselect' => true, 'disable_search' => true],
                        ]) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col col-md-7">
                        <?= $form->field($model, 'fullname')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col col-md-5">
                        <?= $form->field($model, 'language')->widget(ChosenSelect::className(), [
                            'items'         => Config::getLanguageOptions(),
                            'pluginOptions' => ['width' => '100%', 'allow_single_deselect' => true, 'disable_search' => true],
                        ]) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col col-md-7">
                        <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col col-md-5">
                        <?= $form->field($model, 'confirmation', ['labelOptions' => ['class' => 'invisible']])->passwordInput(['maxlength' => true])->label() ?>
                    </div>
                </div>

                <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'telephone')->textInput(['maxlength' => true, 'class' => 'mobile-phone form-control']) ?>
            </div>
            <div class="panel-footer text-right">
                <?= Html::submitButton(__('Save'), ['class' => 'btn btn-primary']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
