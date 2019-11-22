<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

use backend\components\View;
use backend\widgets\AceEditorWidget;
use common\components\Config;
use common\models\Ad;
use trntv\filekit\widget\Upload;
use trntv\yii\datetime\DateTimeWidget;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii2mod\chosen\ChosenSelect;

/* @var $this View */
/* @var $model common\models\Ad */

$this->title                   = $model->isNewRecord ? __('Create Advertising') : $model->title;
$this->params['breadcrumbs'][] = ['url' => ['adv/index'], 'label' => __('Manage Advertising')];
$this->params['breadcrumbs'][] = $this->title;
$user                          = $this->context->_user();

$ic = $model->type == Ad::TYPE_CODE ? true : false;
$ii = $model->type == Ad::TYPE_IMAGE ? true : false;
?>
    <div class="user-create">
        <div class="user-form">
            <?php $form = ActiveForm::begin(['enableAjaxValidation' => true]); ?>
            <div class="row">
                <div class="col col-md-9">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h4><?= __('Advertising Information') ?></h4>
                        </div>
                        <div class="panel-body">
                            <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>

                    <div class="panel-group" id="accordion">
                        <div class="panel panel-default">
                            <div class="panel-heading" id="headingOne">
                                <label data-toggle="collapse" data-target="#collapse_type_image"
                                       data-parent="#accordion">
                                    <input type="radio" name="Ad[type]"
                                           value="<?= Ad::TYPE_IMAGE ?>" <?= $model->type == Ad::TYPE_IMAGE ? 'checked' : '' ?>/> <?= __('Image') ?>
                                </label>
                            </div>
                            <div id="collapse_type_image"
                                 class="panel-collapse collapse <?= $model->type == Ad::TYPE_IMAGE ? 'in' : '' ?>"
                                 aria-labelledby="headingOne">
                                <div class="panel-body ad-images">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <?= $form->field($model, 'url')->textInput(['maxlength' => true]) ?>
                                        </div>
                                        <div class="col-md-12">
                                            <?= $form->field($model, 'image')
                                                     ->widget(Upload::className(),
                                                              [
                                                                  'url'              => ['file-storage/upload', 'type' => 'ad'],
                                                                  'acceptFileTypes'  => new JsExpression('/(\.|\/)(jpe?g|png|gif)$/i'),
                                                                  'sortable'         => true,
                                                                  'maxFileSize'      => 10 * 1024 * 1024, // 10 MiB
                                                                  'maxNumberOfFiles' => 1,
                                                                  'clientOptions'    => [],
                                                              ]
                                                     )->label() ?>
                                        </div>
                                        <div class="col-md-12">
                                            <?= $form->field($model, 'image_mobile')
                                                     ->widget(Upload::className(),
                                                              [
                                                                  'url'              => ['file-storage/upload', 'type' => 'ad'],
                                                                  'acceptFileTypes'  => new JsExpression('/(\.|\/)(jpe?g|png|gif)$/i'),
                                                                  'sortable'         => true,
                                                                  'maxFileSize'      => 10 * 1024 * 1024, // 10 MiB
                                                                  'maxNumberOfFiles' => 1,
                                                                  'clientOptions'    => [],
                                                              ]
                                                     )->label() ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading" id="headingTwo">
                                <label data-toggle="collapse" data-target="#collapse_type_code"
                                       data-parent="#accordion">
                                    <input type="radio" name="Ad[type]"
                                           value="<?= Ad::TYPE_CODE ?>" <?= $model->type == Ad::TYPE_CODE ? 'checked' : '' ?>/> <?= __('Code') ?>
                                </label>
                            </div>
                            <div id="collapse_type_code"
                                 class="panel-collapse collapse <?= $model->type == Ad::TYPE_CODE ? 'in' : '' ?>"
                                 aria-labelledby="headingTwo">
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <?= $form->field($model, 'code')
                                                     ->widget(AceEditorWidget::className())->label('Desktop code') ?>
                                        </div>
                                        <div class="col-md-12">
                                            <?= $form->field($model, 'code_mobile')
                                                     ->widget(AceEditorWidget::className())->label('Mobile code') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
                <div class="col col-md-3 page_settings" id="panel_settings">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4><?= __('Settings') ?></h4>
                        </div>
                        <div class="panel-body">

                            <?= $form->field($model, 'status')->widget(ChosenSelect::className(), [
                                'items'         => Ad::getStatusOptions(),
                                'pluginOptions' => ['width' => '100%', 'allow_single_deselect' => true, 'disable_search' => true],
                            ]) ?>
                            <?= $form->field($model, 'limit_click')->textInput(['type' => 'number', 'min' => 0]) ?>
                            <?= $form->field($model, 'limit_view')->textInput(['type' => 'number', 'min' => 0]) ?>

                            <div class="form-group ">
                                <?= $form->field($model, 'date_from', [
                                    'options' => [
                                        'value' => $model->getDateFromSeconds(),
                                    ],
                                ])->hiddenInput(['id' => 'date_from_time']) ?>

                                <?php $time = $model->getDateFromSeconds() ?>
                                <?= DateTimeWidget::widget([
                                                               'id'               => 'widget_date_from',
                                                               'locale'           => Yii::$app->language == Config::LANGUAGE_UZBEK ? 'uz-latn' : (Yii::$app->language == Config::LANGUAGE_CYRILLIC ? 'uz' : 'ru'),
                                                               'model'            => $model,
                                                               'name'             => 'date_from_time',
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
                                <?= $form->field($model, 'date_to', [
                                    'options' => [
                                        'value' => $model->getDateToSeconds(),
                                    ],
                                ])->hiddenInput(['id' => 'date_to_time']) ?>

                                <?php $time = $model->getDateToSeconds() ?>
                                <?= DateTimeWidget::widget([
                                                               'id'               => 'widget_date_to',
                                                               'locale'           => Yii::$app->language == Config::LANGUAGE_UZBEK ? 'uz-latn' : (Yii::$app->language == Config::LANGUAGE_CYRILLIC ? 'uz' : 'ru'),
                                                               'model'            => $model,
                                                               'name'             => 'date_to_time',
                                                               'value'            => $time ? Yii::$app->formatter->asDatetime($time, 'dd.MM.yyyy, HH:mm') : null,
                                                               'containerOptions' => [],
                                                               'clientEvents'     => [
                                                                   'dp.change' => new JsExpression('function(d){
                                                               time = d.date._d.getTime() / 1000;
                                                               $("#date_to_time").val(Math.round(time))
                                                            }'),
                                                               ],
                                                           ]) ?>

                            </div>
                        </div>
                        <div class="panel-footer">
                            <div class="text-right">
                                <?php if ($model->getId()): ?>
                                    <?= Html::a(__('Delete'), ['adv/delete', 'id' => $model->getId()], ['class' => 'btn btn-danger btn-delete', 'data-confirm' => __('Are you sure to delete?')]) ?>
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