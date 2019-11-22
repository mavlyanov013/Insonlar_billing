<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

use backend\components\View;
use common\models\Appeal;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;

/* @var $this View */
/* @var $model common\models\Appeal */

$this->title                   = $model->isNewRecord ? __('View Appeal') : $model->number;
$this->params['breadcrumbs'][] = ['url' => ['appeal/index'], 'label' => __('Manage Appeals')];
$this->params['breadcrumbs'][] = $this->title;
$user                          = $this->context->_user();
$comments                      = \common\models\AppealHistory::findByAppeal($model);
$statuses                      = Appeal::getStatusOptions()
?>
<div class="user-create">
    <div class="user-form">
        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="pull-left">
                            <?= __('Murojaat', ['number' => $model->number]) ?>
                        </h4>
                        <button class="pull-right btn btn-success" id="print" data-action="<?= \yii\helpers\Url::to(['view', 'id' => $model->id, 'print' => 1]) ?>"><i class="fa fa-print"></i></button>
                    </div>
                    <div class="panel-body">
                        <table class="table">
                            <tbody>
                            <tr>
                                <th><?= __('Ariza raqami') ?>:</th>
                                <td><?= $model->number ?></td>
                            </tr>
                            <tr>
                                <th><?= __('Statusi') ?>:</th>
                                <td><?= $model->getStatusLabel() ?></td>
                            </tr>
                            <tr>
                                <th><?= __('Yuborildi') ?>:</th>
                                <td><?= Yii::$app->formatter->asDatetime($model->created_at->getTimestamp()) ?></td>
                            </tr>

                            <tr>
                                <th><?= __('Yangilandi') ?>:</th>
                                <td><?= Yii::$app->formatter->asDatetime($model->updated_at->getTimestamp()) ?></td>
                            </tr>

                            <tr>
                                <th><?= __('Email') ?>:</th>
                                <td><a href="mailto:<?= $model->email ?>"><?= $model->email ?></a> </td>
                            </tr>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="pull-left">
                            <?= __('Bola haqida ma\'lumotlar') ?>
                        </h4>
                    </div>
                    <div class="panel-body">
                        <table class="table">
                            <tbody>
                            <tr>
                                <th><?= __('Ism-sharifi') ?>:</th>
                                <td><?= $model->fullname ?></td>
                            </tr>
                            <tr>
                                <th><?= __('Tug\'ilgan yili') ?>:</th>
                                <td><?= $model->year ?></td>
                            </tr>

                            <tr>
                                <th><?= __('Manzili') ?>:</th>
                                <td><?= $model->address ?></td>
                            </tr>

                            <tr>
                                <th><?= __('Diagnoz') ?>:</th>
                                <td><?= $model->diagnose ?></td>
                            </tr>

                            <tr>
                                <th><?= __('Telefon') ?>:</th>
                                <td><?= $model->phone ?>; <?= $model->phone2 ?></td>
                            </tr>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4><?= __('Izoh') ?></h4>
                    </div>
                    <div class="panel-body">
                        <?= nl2br($model->text) ?>
                    </div>
                </div>
            </div>

            <?php Pjax::begin(['id' => 'comment-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4><?= __('Murojaat holat') ?></h4>
                    </div>
                    <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'options' => ['data-pjax' => 1]]); ?>
                    <table class="table ">
                        <thead>
                        <tr>
                            <th width="80%"><?= __('Izoh') ?></th>
                            <th width="20%"><?= __('Sana') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($comments as $orderComment): ?>
                            <tr>
                                <td>
                                    <p class="">
                                        <?php
                                        if ($orderComment->admin) {
                                            $name = $orderComment->admin->getFullname();
                                            echo "<b>$name:</b><br>";
                                        }
                                        ?>
                                        <?php if ($orderComment->status_after != $orderComment->status_before): ?>
                                            <?= __('Murojat statusi {b}{before}{bc}dan {b}{after}{bc}ga o\'zgartirildi',
                                                [
                                                    'before' => isset($statuses[$orderComment->status_before]) ? $statuses[$orderComment->status_before] : $orderComment->status_before,
                                                    'after'  => isset($statuses[$orderComment->status_after]) ? $statuses[$orderComment->status_after] : $orderComment->status_after,
                                                ]) ?>
                                        <?php else: ?>
                                            <?= __('Murojaat statusi o\'zgarmadi') ?>
                                        <?php endif; ?>
                                    </p>
                                    <?php if (!empty($orderComment->comment)): ?>
                                        <p class="pre ">
                                            <?= nl2br($orderComment->comment) ?>
                                        </p>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <p class="text-muted">
                                        <?= Yii::$app->formatter->asDatetime($orderComment->created_at->getTimestamp()) ?>
                                    </p>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if ($this->_user()->canAccessToResource('appeal/status')): ?>
                        <div class="panel-body">
                            <?php \common\widgets\Alert::widget([]) ?>
                            <?= $form->field($comment, 'comment')->textarea(['maxlength' => true, 'rows' => 6]) ?>
                        </div>
                        <div class="panel-footer">
                            <div class="row">
                                <div class="col col-sm-7"></div>
                                <div class="col col-sm-3">
                                    <?= $form->field($comment, 'status_after', ['template' => '{input}{error}'])
                                             ->widget(ChosenSelect::className(), [
                                                 'items'           => $model->getNextStatusArray(),
                                                 'withPlaceHolder' => false,
                                                 'pluginOptions'   => [
                                                     'allow_single_deselect' => false,
                                                     'disable_search'        => true,
                                                 ],
                                             ]) ?>
                                </div>
                                <div class="col col-sm-2">
                                    <?= Html::submitButton(__('Save'), ['class' => 'btn btn-primary btn-full', 'style' => 'width:100%']) ?>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    <?php endif; ?>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
            <?php Pjax::end() ?>
        </div>
    </div>
</div>
<script>
    var btn = document.getElementById('print');
    btn.addEventListener('click', function (e) {
        var $window = window.open(btn.dataset.action);

        $window.onafterprint = function(){
            $window.close()
        }
    })
</script>