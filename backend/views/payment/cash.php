<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

use common\components\Config;
use common\models\Admin;
use common\models\payment\Payment;
use common\models\Volunteer;
use dosamigos\tinymce\TinyMce;
use trntv\filekit\widget\Upload;
use trntv\yii\datetime\DateTimeWidget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii2mod\chosen\ChosenSelect;

/* @var $this yii\web\View */
/* @var $model common\models\payment\Payment */


$this->title                   = $model->isNewRecord ? __('Create Cash Payment') : ($model->transaction_id ? $model->transaction_id : $model->id);
$this->params['breadcrumbs'][] = ['url' => ['payment/index'], 'label' => __('Manage Payments')];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php $form = ActiveForm::begin([]); ?>
<div class="row">
    <div class="col col-md-9">
        <div class="panel panel-primary">
            <div class="panel-heading ">
                <h4><?= __('Payment Information') ?></h4>
            </div>
            <div class="panel-body">
                <?= $form->field($model, 'amount')->textInput(['maxlength' => true, 'type' => 'number'])->label(__('Amount {som}')) ?>

                <?= $form->field($model, 'user_data')->textInput(['maxlength' => true])->label(__('Payer Name or Phone')) ?>

                <?= $form->field($model, 'details')->textarea(['maxlength' => true, 'rows' => 7])->label(__('Details')) ?>
            </div>
        </div>

    </div>
    <div class="col col-md-3">
        <div class="panel panel-default">
            <div class="panel-heading ">
                <h4><?= __('Payment Settings') ?></h4>
            </div>
            <div class="panel-body">
                <?= $form->field($model, 'status')->widget(ChosenSelect::className(), [
                    'items'         => Payment::getCashStatusOptions(),
                    'pluginOptions' => ['width' => '100%', 'allow_single_deselect' => true, 'disable_search' => true],
                ]) ?>

                <?= $form->field($model, 'time')->widget(DateTimeWidget::class, [
                    'locale'               => Yii::$app->language == Config::LANGUAGE_UZBEK ? 'uz-latn' : (Yii::$app->language == Config::LANGUAGE_CYRILLIC ? 'uz' : 'ru'),
                    'phpDatetimeFormat'    => 'dd/MM/yyyy HH:mm',
                    'momentDatetimeFormat' => 'DD-MM-Y HH:mm',
                    'value'                => Yii::$app->formatter->asDatetime(time(), 'dd/MM/yyyy HH:mm'),
                ]) ?>

                <?= $form->field($model, 'image')->widget(Upload::className(), [
                    'url'              => ['file-storage/upload', 'type' => 'blogger-image'],
                    'acceptFileTypes'  => new JsExpression('/(\.|\/)(jpe?g|png)$/i'),
                    'maxFileSize'      => 10 * 1024 * 1024, // 10 MiB
                    'maxNumberOfFiles' => 1,
                    'multiple'         => false,
                    'clientOptions'    => [],
                ]) ?>

            </div>
            <div class="panel-footer text-right">
                <?php if ($model->getId() && $this->_user()->canAccessToResource('payment/cash-delete')): ?>
                    <?= Html::a(__('Delete'), ['volunteer/delete', 'id' => $model->getId()], ['class' => 'btn btn-danger btn-delete', 'data-confirm' => __('Are you sure to delete?')]) ?>
                <?php endif; ?>
                <?= Html::submitButton(__('Save'), ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>

<?php
if (!$model->isNewRecord)
    $this->registerJs('
    $("#change_password").on("change", function () {
        $("input[name=\'Blogger[password]\'],input[name=\'Blogger[confirmation]\']").attr("disabled", !this.checked);
    });
')
?>

