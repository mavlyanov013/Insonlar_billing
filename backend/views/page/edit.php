<?php

use backend\components\View;
use common\models\Page;
use dosamigos\tinymce\TinyMce;
use trntv\filekit\widget\Upload;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii2mod\chosen\ChosenSelect;

/* @var $this View */
/* @var $model common\models\Page */

$this->registerJs('
$(\'#page-title\').blur(function () {
    if ($(\'#page-url\').val().length < 2)$(\'#page-url\').val(convertToSlug($(this).val()));
});
');

$this->title                   = $model->isNewRecord ? __('Create Page') : $model->title;
$this->params['breadcrumbs'][] = ['url' => ['page/index'], 'label' => __('Manage Pages')];
$this->params['breadcrumbs'][] = $this->title;
$user                          = $this->context->_user();

?>
<?php $form = ActiveForm::begin(['enableAjaxValidation' => true]); ?>
    <div class="row">
        <div class="col col-md-9">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h4><?= __('Page Information') ?></h4>
                </div>
                <div class="panel-body">
                    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($model, 'url')->textInput(['maxlength' => true, 'placeholder' => __('Page Link')])->label(false) ?>

                    <?php if ($model->type != 'block'): ?>
                        <?= $form->field($model, 'content')->widget(TinyMce::className(), [
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
                                'content_style'     => 'body {max-width: 911px; margin: 5px auto;}.mce-content-body img{width:98%; height:98%}figure.image{margin:0px;width:100%}',
                                'images_upload_url' => Url::to(['file-storage/upload', 'type' => 'content-image', 'fileparam' => 'file']),
                                'preview_url'       => Url::to('@frontendUrl/site/p/' . $model->getId()),
                                'toolbar1'          => "undo redo | styleselect blockquote | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist | link image table code fullscreen",
                            ],
                            'options'       => ['rows' => 25],
                        ]) ?>
                    <?php else: ?>
                        <?= $form->field($model, 'content')->widget(\backend\widgets\AceEditorWidget::class) ?>
                    <?php endif ?>

                </div>

            </div>
        </div>
        <div class="col col-md-3 page_settings" id="panel_settings">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4><?= __('Settings') ?></h4>
                </div>
                <div class="panel-body">
                    <?php if ($model->getId()): ?>
                        <?= $this->renderFile('@backend/views/layouts/_convert.php', ['link' => Url::to(['page/edit', 'id' => $model->getId(), 'convert' => 1])]) ?>
                    <?php endif; ?>

                    <?= $form->field($model, 'type')->widget(ChosenSelect::className(), [
                        'items'         => Page::getTypeArray(),
                        'pluginOptions' => ['width' => '100%', 'allow_single_deselect' => true, 'disable_search' => true],
                    ]) ?>

                    <?= $form->field($model, 'status')->widget(ChosenSelect::className(), [
                        'items'         => Page::getStatusArray(),
                        'pluginOptions' => ['width' => '100%', 'allow_single_deselect' => true, 'disable_search' => true],
                    ]) ?>
                    <?= $form->field($model, 'image')->widget(Upload::className(), [
                        'url'              => ['file-storage/upload', 'type' => 'post-image'],
                        'acceptFileTypes'  => new JsExpression('/(\.|\/)(jpe?g|png)$/i'),
                        'maxFileSize'      => 10 * 1024 * 1024, // 10 MiB
                        'maxNumberOfFiles' => 1,
                        'clientOptions'    => [],
                    ])->label(false) ?>
                </div>
                <div class="panel-footer">
                    <div class="text-right">
                        <?php if ($model->getId()): ?>
                            <?= Html::a(__('Delete'), ['page/delete', 'id' => $model->getId()], ['class' => 'btn btn-danger btn-delete', 'data-confirm' => __('Are you sure to delete?')]) ?>
                        <?php endif; ?>
                        <?= Html::submitButton(__('Save'), ['class' => 'btn btn-primary']) ?>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>
<?php ActiveForm::end(); ?>
<?php
$this->registerJs('
    $("#panel_settings").theiaStickySidebar({
        additionalMarginTop: 70,
        additionalMarginBottom: 20
    });
')
?>