<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

use common\components\Config;
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
/* @var $model common\models\Volunteer */

$this->title                   = $model->isNewRecord ? __('Create Volunteer') : $model->fullname;
$this->params['breadcrumbs'][] = ['url' => ['volunteer/index'], 'label' => __('Manage Volunteers')];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php $form = ActiveForm::begin([]); ?>
<div class="row">
    <div class="col col-md-9">
        <div class="panel panel-primary">
            <div class="panel-heading ">
                <h4><?= __('Volunteer Information') ?></h4>
            </div>
            <div class="panel-body">
                <?= $form->field($model, 'fullname')->textInput(['maxlength' => true]) ?>

                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'phone')->textInput(['maxlength' => true, 'class' => 'mobile-phone form-control']) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'job')->textInput(['maxlength' => true, 'class' => 'form-control']) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'gender')->widget(ChosenSelect::class, [
                            'items' => Volunteer::getGenderOptions(),
                        ]) ?>
                    </div>
                </div>

                <?= $form->field($model, 'description')->textarea(['maxlength' => true, 'rows' => 3])->label(__('Purpose')) ?>

                <?= $form->field($model, 'about')->widget(TinyMce::className(), [
                    'clientOptions' => [
                        'plugins'           => [
                            "advlist autolink lists link imagetools image charmap print hr anchor pagebreak",
                            "searchreplace wordcount visualblocks visualchars code fullscreen",
                            "insertdatetime media nonbreaking save table contextmenu directionality",
                            "emoticons template paste textcolor colorpicker textpattern",
                        ],
                        'image_title'       => true,
                        'image_class_list'  => 'img-responsive',
                        'image_dimensions'  => false,
                        'automatic_uploads' => true,
                        'image_caption'     => true,
                        'content_style'     => 'body {max-width: 768px; margin: 5px auto;}.mce-content-body img{width:98%}figure.image{margin:0px;width:100%}',
                        'images_upload_url' => Url::to(['file-storage/upload', 'type' => 'content-image', 'fileparam' => 'file']),
                        'preview_url'       => Url::to('@frontendUrl/site/p/' . $model->getId()),
                        'toolbar1'          => "undo redo | styleselect blockquote |  bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | link code fullscreen",
                    ],
                    'options'       => ['rows' => 10],
                ]) ?>
            </div>

        </div>

    </div>
    <div class="col col-md-3">
        <div class="panel panel-default">
            <div class="panel-heading ">
                <h4><?= __('Profile') ?></h4>
            </div>
            <div class="panel-body">
                <?php if ($model->getId()): ?>
                    <?= $this->renderFile('@backend/views/layouts/_convert.php', ['link' => Url::to(['volunteer/update', 'id' => $model->getId(), 'convert' => 1])]) ?>
                <?php endif; ?>

                <?= $form->field($model, 'image')->widget(Upload::className(), [
                    'url'              => ['file-storage/upload', 'type' => 'blogger-image'],
                    'acceptFileTypes'  => new JsExpression('/(\.|\/)(jpe?g|png)$/i'),
                    'maxFileSize'      => 10 * 1024 * 1024, // 10 MiB
                    'maxNumberOfFiles' => 1,
                    'multiple'         => false,
                    'clientOptions'    => [],
                ]) ?>


                <?= $form->field($model, 'type')->widget(ChosenSelect::className(), [
                    'items'           => Volunteer::getTypeOptions(),
                    'withPlaceHolder' => false,
                    'pluginOptions'   => ['width' => '100%', 'allow_single_deselect' => false, 'disable_search' => true],
                ]) ?>

                <?= $form->field($model, 'position')->textInput(['maxlength' => true]) ?>


                <?= $form->field($model, 'status')->widget(ChosenSelect::className(), [
                    'items'           => Volunteer::getStatusOptions(),
                    'withPlaceHolder' => false,
                    'pluginOptions'   => ['width' => '100%', 'allow_single_deselect' => true, 'disable_search' => true],
                ]) ?>

                <?= $form->field($model, 'birthday')->widget(DateTimeWidget::class, [
                    'locale'            => Yii::$app->language == Config::LANGUAGE_UZBEK ? 'uz-latn' : (Yii::$app->language == Config::LANGUAGE_CYRILLIC ? 'uz' : 'ru'),
                    'phpDatetimeFormat' => 'dd/MM/yyyy',
                ]) ?>

            </div>
            <div class="panel-footer text-right">
                <?php if ($model->getId() && $this->_user()->canAccessToResource('volunteer/delete')): ?>
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

