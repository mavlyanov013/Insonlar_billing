<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \backend\models\FormAdminLogin */

$this->title                   = __('Login');
$this->params['breadcrumbs'][] = $this->title;
?>


<?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
<div class="panel panel-primary panel-lg">
    <div class="panel-heading">
        <?= __('Admin Dashboard') ?>
        <div class="pull-right">
            <?= Html::a('<span class="mdi-navigation-more-vert"></span>', ['dashboard/reset'], ['style' => 'color:rgba(255,255,255,.84)']) ?>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col col-lg-12">
                <?= $form->field($model, 'login')->textInput(['placeholder' => 'Login'])->label(false) ?>
                <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Password'])->label(false) ?>


            </div>
        </div>
    </div>
    <div class="panel-footer">

        <?= YII_DEBUG ? '' : $form->field($model, 'reCaptcha')->widget(
            \himiklab\yii2\recaptcha\ReCaptcha::className(),
            []
        )->label(false) ?>

        <div class="row">
            <div class="col col-md-6 checkbo">
                <label class="control-label cb-checkbox" for="rememberMe">
                    <?= __('Remember Me') . Html::checkbox("FormAdminLogin[rememberMe]", false, ['id' => 'rememberMe']) ?>
                </label>
            </div>
            <div class="col col-md-6">
                <?= Html::submitButton('Login', ['class' => 'btn btn-primary btn-block', 'name' => 'login-button']) ?>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
<?php ActiveForm::end(); ?>
<?php
$this->registerJs('
    $(document).ready(function () {
        var input = $("#adminloginform-login");
        input.focus().val(input.val());
    })
')
?>
