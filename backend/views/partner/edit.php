<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

use backend\components\View;
use common\components\Config;
use common\models\Partner;
use dosamigos\tinymce\TinyMce;
use trntv\filekit\widget\Upload;
use trntv\yii\datetime\DateTimeWidget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii2mod\chosen\ChosenSelect;

/* @var $this View */
/* @var $model Partner */

$this->title                   = $model->isNewRecord ? __('Create Partner') : $model->name;
$this->params['breadcrumbs'][] = ['url' => ['partner/index'], 'label' => __('Manage Partners')];
$this->params['breadcrumbs'][] = $this->title;
$user                          = $this->context->_user();

?>
    <div class="user-create">
        <div class="user-form">
            <?php $form = ActiveForm::begin(['enableAjaxValidation' => true]); ?>
            <div class="row">
                <div class="col col-md-9">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h4><?= __('Partner Information') ?></h4>
                        </div>
                        <div class="panel-body">
                            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                            <?= $form->field($model, 'description')->widget(TinyMce::className(), [
                                'clientOptions' => [
                                    'plugins'           => [
                                        "advlist autolink lists link imagetools image charmap print hr anchor pagebreak",
                                        "searchreplace wordcount visualblocks visualchars code fullscreen",
                                        "insertdatetime media nonbreaking save table contextmenu directionality",
                                        "template paste textcolor colorpicker textpattern",
                                    ],
                                    'image_title'       => true,
                                    'image_class_list'  => 'img-responsive',
                                    'image_dimensions'  => false,
                                    'automatic_uploads' => true,
                                    'image_caption'     => true,
                                    'content_style'     => 'body {max-width: 768px; margin: 5px auto;}.mce-content-body img{width:98%; height:98%}figure.image{margin:0px;width:100%}',
                                    'images_upload_url' => Url::to(['file-storage/upload', 'type' => 'content-image', 'fileparam' => 'file']),
                                    'preview_url'       => Url::to('@frontendUrl/site/p/' . $model->getId()),
                                    'toolbar1'          => "undo redo | styleselect blockquote | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist | link image table code fullscreen",
                                ],
                                'options'       => ['rows' => 25],
                            ]) ?>
                        </div>
                    </div>

                </div>
                <div class="col col-md-3 page_settings" id="panel_settings">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4><?= __('Settings') ?></h4>
                        </div>
                        <div class="panel-body">
                            <?= $form->field($model, 'position')->textInput(['maxlength' => true]) ?>

                            <?= $form->field($model, 'status')->widget(ChosenSelect::className(), [
                                'items'         => Partner::getStatusOptions(),
                                'pluginOptions' => ['width' => '100%', 'allow_single_deselect' => true, 'disable_search' => true],
                            ]) ?>
                            <?= $form->field($model, 'logo')->widget(Upload::className(), [
                                'url'              => ['file-storage/upload'],
                                'acceptFileTypes'  => new JsExpression('/(\.|\/)(jpe?g|png)$/i'),
                                'maxFileSize'      => 10 * 1024 * 1024, // 10 MiB
                                'maxNumberOfFiles' => 1,
                                'clientOptions'    => [],
                            ])->label() ?>
                        </div>
                        <div class="panel-footer">
                            <div class="text-right">
                                <?php if ($model->getId()): ?>
                                    <?= Html::a(__('Delete'), ['event/delete', 'id' => $model->getId()], ['class' => 'btn btn-danger btn-delete', 'data-confirm' => __('Are you sure to delete?')]) ?>
                                <?php endif; ?>
                                <?= Html::submitButton(__('Save'), ['class' => 'btn btn-primary']) ?>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
<?php
$this->registerJs('
    $("#panel_settings").theiaStickySidebar({
        additionalMarginTop: 70,
        additionalMarginBottom: 20
    });
')
?>