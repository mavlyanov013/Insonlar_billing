<?php
use common\components\Config;
use common\models\Admin;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii2mod\chosen\ChosenSelect;

/* @var $this yii\web\View */
/* @var $model common\models\Admin */

$this->title                   = __('Update Administrator');
$this->params['breadcrumbs'][] = ['url' => ['/backend/system/translation'], 'label' => __('System')];
$this->params['breadcrumbs'][] = ['url' => ['/backend/admin/index'], 'label' => __('Manage Administrators')];
$this->params['breadcrumbs'][] = $model->fullname;
$user                          = $this->context->_user();
?>

<div class="row">
    <div class="col col-md-8">
        <div class="panel">
            <?php $form = ActiveForm::begin(['validateOnSubmit' => true]); ?>

            <div class="panel-heading border">
                <h4><?= __('Account Information') ?></h4>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col col-md-7">
                        <?= $form->field($model, 'login')->textInput(['maxlength' => true, 'disabled' => $model->isSuperAdmin])->label() ?>
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
                        <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col col-md-5">
                        <?= $form->field($model, 'language')->widget(ChosenSelect::className(), [
                            'items'         => Config::getLanguageOptions(),
                            'pluginOptions' => ['width' => '100%', 'allow_single_deselect' => true, 'disable_search' => true],
                        ]) ?>
                        <?= $form->field($model, 'telephone')->textInput(['maxlength' => true, 'class' => 'mobile-phone form-control']) ?>
                    </div>
                </div>
                <br>
                <?php $label = '<label class="control-label cb-checkbox">' . __('Change Password') . Html::checkbox("Admin[change_password]", false, ['id' => 'change_password']) . '</label>' ?>
                <div class="row checkbo">
                    <div class="col col-md-7">
                        <?= $form->field($model, 'password', ['template' => "$label{input}\n{error}"])->passwordInput(['maxlength' => true, 'value' => '', 'disabled' => 'disabled', 'placeholder' => __('New Password')])->label($label) ?>
                    </div>
                    <div class="col col-md-5">
                        <?= $form->field($model, 'confirmation', ['template' => "<label class=\"control-label cb-checkbox\">&nbsp;</label>{input}\n{error}"])->passwordInput(['maxlength' => true, 'value' => '', 'disabled' => 'disabled', 'placeholder' => __('Password Confirmation')]) ?>
                    </div>
                </div>
            </div>
            <div class="panel-footer text-right">
                <?php if ($user->canAccessToResource('admin/delete')): ?>
                    <?= Html::a(__('Delete'), ['/backend/admin/delete', 'id' => $model->id], ['class' => 'btn btn-danger btn-delete']) ?>
                <?php endif; ?>
                <?= Html::submitButton(__('Update'), ['class' => 'btn btn-primary ']) ?>
            </div>
        </div>
    </div>
    <div class="col col-md-4">
        <div class="panel">
            <div class="panel-body">
                <?= $this->render('resources', ['model' => $model]) ?>
            </div>
            <div class="panel-footer">
                <div class=" pull-right">
                    <?= Html::submitButton(__('Update'), ['class' => 'btn btn-primary ']) ?>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="col col-md-6"></div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<?php
$this->registerJs('
    $("#change_password").on("change", function () {
        $("input[name=\'Admin[password]\'],input[name=\'Admin[confirmation]\']").attr("disabled", !this.checked);
    })
')
?>

