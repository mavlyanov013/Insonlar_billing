<?php

use common\models\SystemMessage;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel SystemMessage */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title                   = __('System Backups');
$this->params['breadcrumbs'][] = ['url' => ['system/index'], 'label' => __('System')];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="translate-index">
    <?php Pjax::begin(['id' => 'backup-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
    <div class="panel panel-default data-grid">
        <div class="panel-heading">
            <div class="row">
                <div class="col col-md-6 col-md-2">
                </div>
                <div class="col col-md-6 col-md-6">
                </div>

                <div class="col col-md-6 col-md-4 text-right">
                    <a data-pjax="0" href="<?= Url::to(['system/snapshot']) ?>" class="btn btn-default"><i
                            class='fa fa-database'></i></a>
                </div>
            </div>
        </div>
        <?= GridView::widget([
                                 'dataProvider' => $dataProvider,
                                 'id'           => 'data-grid',
                                 'layout'       => "{items}\n<div class='panel-footer'>{pager}<div class='clearfix'></div></div>",
                                 'tableOptions' => ['class' => 'table table-striped table-hover'],
                                 'columns'      => [
                                     /* [
                                          'class'           => \yii\grid\CheckboxColumn::className(),
                                          'checkboxOptions' => function ($model, $key, $index, $column) {
                                              return ['value' => $model['name']];
                                          },
                                      ],*/
                                     [
                                         'attribute' => 'name',
                                         'format'    => 'raw',
                                         'value'     => function ($data) {
                                             return Html::a($data['name'], ['/system/backup', 'id' => $data['name']], ['data-pjax' => 0]);
                                         },
                                     ],

                                     [
                                         'attribute' => 'size',
                                     ],
                                     [
                                         'attribute' => 'time',
                                     ],
                                     [
                                         'attribute' => 'action',
                                         'format'    => 'raw',
                                         'value'     => function ($data) {
                                             return Html::a(__('Remove'), ['/system/backup', 'rem' => $data['name']], ['data-pjax' => 0]);
                                         },
                                     ]/*,
                                     [
                                         'attribute' => 'action',
                                         'format'    => 'raw',
                                         'value'     => function ($data) {
                                             return Html::a(__('Restore'), ['/system/backup', 'res' => $data['name']], ['data-pjax' => 0]);
                                         },
                                     ],*/
                                 ],
                             ]); ?>
    </div>
    <?php Pjax::end() ?>
</div>