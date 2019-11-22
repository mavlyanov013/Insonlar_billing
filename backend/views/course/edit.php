<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

use common\models\Course;
use dosamigos\tinymce\TinyMce;
use trntv\filekit\widget\Upload;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;

/* @var $this yii\web\View */
/* @var $model Course */


$this->title                   = $model->isNewRecord ? __('Create Course') : $model->name;
$this->params['breadcrumbs'][] = ['url' => ['course/index'], 'label' => __('Manage Courses')];
$this->params['breadcrumbs'][] = $this->title;
$user                          = $this->context->_user();
$this->registerJs('initModule();');
?>
<?php Pjax::begin(['enablePushState' => false]) ?>
<div class="row">
    <?php $form = ActiveForm::begin(['enableAjaxValidation' => false]); ?>
    <div class="col col-md-9">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h4><?= __('Course Information') ?></h4>
            </div>
            <div class="panel-body">
                <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'description')->textarea(['rows' => 6, 'placeholder' => __('Short Information')])->label() ?>
                <?= $form->field($model, 'content')->widget(TinyMce::className(), [
                    'clientOptions' => [
                        'plugins'                 => [
                            "advlist autolink lists link imagetools image charmap print hr anchor pagebreak",
                            "searchreplace wordcount visualblocks visualchars code fullscreen",
                            "insertdatetime media nonbreaking save table contextmenu directionality",
                            "emoticons template paste textcolor colorpicker textpattern",
                        ],
                        'extended_valid_elements' => 'script[language|type|src]',
                        'image_title'             => true,
                        'image_class_list'        => 'img-responsive',
                        'image_dimensions'        => false,
                        'automatic_uploads'       => true,
                        'image_caption'           => true,
                        'content_style'           => 'body {max-width: 768px; margin: 5px auto;}.mce-content-body img{width:98%; height:98%}figure.image{margin:0px;width:100%}',
                        'images_upload_url'       => Url::to(['file-storage/upload', 'type' => 'content-image', 'fileparam' => 'file']),
                        'toolbar1'                => "undo redo | styleselect blockquote | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | insertfile link image fullscreen ",
                    ],
                    'options'       => ['rows' => 10],
                ]) ?>

                <?= $form->field($model, 'files')->widget(Upload::class, [
                    'url'              => ['file-storage/upload'],
                    'acceptFileTypes'  => new JsExpression('/(\.|\/)(jpe?g|png|docx?|zip|rar|pdf|txt|xlsx?)$/i'),
                    'maxFileSize'      => 20 * 1024 * 1024, // 10 MiB
                    'maxNumberOfFiles' => 20,
                    'clientOptions'    => [],
                ])->label() ?>

            </div>

        </div>
    </div>
    <div class="col col-md-3 post_settings">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4><?= __('Settings') ?></h4>
            </div>
            <div class="panel-body">
                <?= $form->field($model, 'contact')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'status')->widget(ChosenSelect::className(), [
                    'items'         => Course::getStatusOptions(),
                    'pluginOptions' => ['width' => '100%'],
                ]) ?>

                <?= $form->field($model, 'image')->widget(
                    Upload::className(),
                    [
                        'url'              => ['file-storage/upload'],
                        'acceptFileTypes'  => new JsExpression('/(\.|\/)(jpe?g|png)$/i'),
                        'sortable'         => true,
                        'maxFileSize'      => 5 * 1024 * 1024, // 5 MiB
                        'maxNumberOfFiles' => 1,
                        'clientOptions'    => ['height' => '120%'],
                    ]
                ) ?>
            </div>
            <div class="panel-footer">
                <div class="text-right">
                    <?= Html::submitButton(__('Save'), ['class' => 'btn btn-primary']) ?>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<script type="application/javascript">

    function initModule() {
        $('.post_settings').theiaStickySidebar({
            additionalMarginTop: 70,
            additionalMarginBottom: 20
        });
    }

</script>
<?php Pjax::end(); ?>
