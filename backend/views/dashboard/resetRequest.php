<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \backend\models\AdminLoginForm */

$this->title                   = __('Reset Password');
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $form = ActiveForm::begin(['id' => 'reset-form', 'enableAjaxValidation' => true,]); ?>
<div class="panel panel-primary panel-lg">
    <div class="panel-heading">
        <?= __('Reset Password') ?>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col col-lg-12">
                <?= $form->field($model, 'email')->textInput(['placeholder' => __('Enter Account Email')])->label(false) ?>
            </div>
        </div>
    </div>
    <div class="panel-footer">
        <div class="pull-right">
            <?= Html::submitButton('Reset', ['class' => 'btn btn-primary pull-right', 'name' => 'login-button']) ?>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
<?php ActiveForm::end(); ?>
<?php
$this->registerJs('
    $(document).ready(function () {
        var input = $("#admin-email");
        input.focus().val(input.val());
    })
')
?>
