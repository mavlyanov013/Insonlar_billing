<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

use backend\components\View;
use backend\widgets\AceEditorWidget;
use common\components\Config;
use common\models\Ad;
use common\models\Event;
use dosamigos\tinymce\TinyMce;
use trntv\filekit\widget\Upload;
use trntv\yii\datetime\DateTimeWidget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii2mod\chosen\ChosenSelect;

/* @var $this View */
/* @var $model common\models\Event */

$this->title                   = $model->isNewRecord ? __('Create Event') : $model->name;
$this->params['breadcrumbs'][] = ['url' => ['event/index'], 'label' => __('Manage Event')];
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
                            <h4><?= __('Event Information') ?></h4>
                        </div>
                        <div class="panel-body">
                            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                            <?= $form->field($model, 'address')->textarea(['maxlength' => true]) ?>
                            <?= $form->field($model, 'description')->textarea(['rows' => 6, 'maxlength' => true]) ?>
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
                                    'content_style'     => 'body {max-width: 768px; margin: 5px auto;}.mce-content-body img{width:98%; height:98%}figure.image{margin:0px;width:100%}',
                                    'images_upload_url' => Url::to(['file-storage/upload', 'type' => 'content-image', 'fileparam' => 'file']),
                                    'preview_url'       => Url::to('@frontendUrl/site/p/' . $model->getId()),
                                    'toolbar1'          => "undo redo | styleselect blockquote | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist | link image table code fullscreen",
                                ],
                                'options'       => ['rows' => 25],
                            ]) ?>
                            <?= $form->field($model, 'gallery')->widget(Upload::className(), [
                                'url'              => ['file-storage/upload', 'type' => 'event-image'],
                                'acceptFileTypes'  => new JsExpression('/(\.|\/)(jpe?g|png)$/i'),
                                'maxFileSize'      => 10 * 1024 * 1024, // 10 MiB
                                'maxNumberOfFiles' => 10,
                                'clientOptions'    => [],
                            ])->label() ?>
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
                                <?= $this->renderFile('@backend/views/layouts/_convert.php', ['link' => Url::to(['event/edit', 'id' => $model->getId(), 'convert' => 1])]) ?>
                            <?php endif; ?>
                            <?= $form->field($model, 'status')->widget(ChosenSelect::className(), [
                                'items'         => Event::getStatusOptions(),
                                'pluginOptions' => ['width' => '100%', 'allow_single_deselect' => true, 'disable_search' => true],
                            ]) ?>
                            <?= $form->field($model, 'coordinates')->textInput([]) ?>

                            <div class="form-group ">
                                <?= $form->field($model, 'from_date', ['options' => ['class' => '']])
                                         ->hiddenInput(['id' => 'date_from_time', 'value' => $model->getTimeSeconds('from_date'),]) ?>

                                <?php $time = $model->getTimeSeconds('from_date') ?>
                                <?= DateTimeWidget::widget([
                                                               'id'               => 'widget_from_date',
                                                               'locale'           => Yii::$app->language == Config::LANGUAGE_UZBEK ? 'uz-latn' : (Yii::$app->language == Config::LANGUAGE_CYRILLIC ? 'uz' : 'ru'),
                                                               'model'            => $model,
                                                               'name'             => 'from_date_time',
                                                               'value'            => $time ? Yii::$app->formatter->asDatetime($time, 'dd.MM.yyyy, HH:mm') : null,
                                                               'containerOptions' => [],
                                                               'clientEvents'     => [
                                                                   'dp.change' => new JsExpression('function(d){
                                                                                       time = d.date._d.getTime() / 1000;
                                                                                       $("#date_from_time").val(Math.round(time))
                                                                                    }'),
                                                               ],
                                                           ]) ?>
                            </div>
                            <div class="form-group">
                                <?= $form->field($model, 'to_date', ['options' => ['class' => '']])
                                         ->hiddenInput(['id' => 'date_to_time', 'value' => $model->getTimeSeconds('to_date')]) ?>

                                <?php $time = $model->getTimeSeconds('to_date') ?>
                                <?= DateTimeWidget::widget([
                                                               'id'               => 'widget_to_date',
                                                               'locale'           => Yii::$app->language == Config::LANGUAGE_UZBEK ? 'uz-latn' : (Yii::$app->language == Config::LANGUAGE_CYRILLIC ? 'uz' : 'ru'),
                                                               'model'            => $model,
                                                               'name'             => 'to_date_time',
                                                               'value'            => $time ? Yii::$app->formatter->asDatetime($time, 'dd.MM.yyyy, HH:mm') : null,
                                                               'containerOptions' => ['class' => 'mb15'],
                                                               'clientEvents'     => [
                                                                   'dp.change' => new JsExpression('function(d){
                                                                                       time = d.date._d.getTime() / 1000;
                                                                                       $("#date_to_time").val(Math.round(time))
                                                                                    }'),
                                                               ],
                                                           ]) ?>
                                <?= $form->field($model, 'image')->widget(Upload::className(), [
                                    'url'              => ['file-storage/upload', 'type' => 'post-image'],
                                    'acceptFileTypes'  => new JsExpression('/(\.|\/)(jpe?g|png)$/i'),
                                    'maxFileSize'      => 10 * 1024 * 1024, // 10 MiB
                                    'maxNumberOfFiles' => 1,
                                    'clientOptions'    => [],
                                ])->label(false) ?>
                            </div>
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
            <?php ActiveForm::end(); ?>
        </div>
    </div>
<?php
$this->registerJs('
    $("#panel_settings").theiaStickySidebar({
        additionalMarginTop: 70,
        additionalMarginBottom: 20
    });
')
?>