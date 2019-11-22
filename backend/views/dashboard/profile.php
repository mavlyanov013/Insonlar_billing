<?php
use common\components\Config;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii2mod\chosen\ChosenSelect;

/* @var $this yii\web\View */
/* @var $model common\models\Admin */

$this->title                   = __('My Profile');
$this->params['breadcrumbs'][] = $model->fullname;

?>

<?php $form = ActiveForm::begin(['enableAjaxValidation' => true,]); ?>
<div class="row">
    <div class="col col-md-6">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3><?= __('My Profile') ?></h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col col-md-7">
                        <?= $form->field($model, 'login')->textInput(['maxlength' => true, 'disabled' => true])->label() ?>
                    </div>
                    <div class="col col-md-5">
                        <?= $form->field($model, 'language')->widget(ChosenSelect::className(), [
                            'items'         => Config::getLanguageOptions(),
                            'pluginOptions' => ['width' => '100%', 'allow_single_deselect' => true, 'disable_search' => true],
                        ]) ?>
                    </div>
                </div>


                <?= $form->field($model, 'fullname')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'telephone')->textInput(['maxlength' => true, 'class' => 'mobile-phone form-control']) ?>

                <br>
                <?php $label = '<label class="control-label cb-checkbox">' . __('Change Password') . Html::checkbox("Admin[change_password]", false, ['id' => 'change_password']) . '</label>' ?>
                <div class="row checkbo">
                    <div class="col col-md-6">
                        <?= $form->field($model, 'password', ['template' => "$label{input}\n{error}"])->passwordInput(['maxlength' => true, 'value' => '', 'disabled' => 'disabled', 'placeholder' => __('New Password')])->label($label) ?>
                    </div>
                    <div class="col col-md-6">
                        <?= $form->field($model, 'confirmation', ['template' => "<label class=\"control-label cb-checkbox\">&nbsp;</label>{input}\n{error}"])->passwordInput(['maxlength' => true, 'value' => '', 'disabled' => 'disabled', 'placeholder' => __('Password Confirmation')]) ?>
                    </div>
                </div>

            </div>
            <div class="panel-footer text-right">
                <?= Html::submitButton(__('Update'), ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    </div>
    <div class="col col-md-3"></div>
</div>
<?php ActiveForm::end(); ?>
<?php
$this->registerJs('
    $("#change_password").on("change", function () {
        $("input[name=\'Admin[password]\'],input[name=\'Admin[confirmation]\']").attr("disabled", !this.checked);
    })
')

?>

