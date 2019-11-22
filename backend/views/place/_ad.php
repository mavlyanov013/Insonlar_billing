<?php

use yii\bootstrap\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $dataProvider               \common\models\Ad */
?>
<?php Pjax::begin(['id' => 'ads-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<?= GridView::widget([
                         'dataProvider' => $dataProvider,
                         'id'           => 'ads-grid-list',
                         'layout'       => "{items}\n<div class='panel-footer'>{pager}<div class='clearfix'></div></div>",
                         'tableOptions' => ['class' => 'table table-striped table-hover '],
                         'columns'      => [
                             ['class' => 'yii\grid\CheckboxColumn'],
                             [
                                 'attribute' => 'title',
                                 'format'    => 'raw',
                                 'value'     => function ($model) {
                                     return $model->title;
                                 },
                             ],
                             [
                                 'attribute' => 'views',
                                 'format'    => 'integer',
                                 'value'     => function ($model) {
                                     return $model->views;
                                 },
                             ],
                             [
                                 'attribute' => 'clicks',
                                 'format'    => 'integer',
                                 'value'     => function ($model) {
                                     return $model->clicks;
                                 },
                             ],
                             [
                                 'attribute' => 'status',
                                 'format'    => 'raw',
                                 'value'     => function ($model) {
                                     return $model->getStatusLabel();
                                 },
                             ],
                             [
                                 'attribute' => 'created_at',
                                 'format'    => 'raw',
                                 'value'     => function ($data) {
                                     return Yii::$app->formatter->asDatetime($data->created_at->getTimestamp());
                                 },
                             ],
                         ],
                     ]); ?>
<?php Pjax::end() ?>
