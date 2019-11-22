<?php

use backend\widgets\GridView;
use common\models\Ad;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model Ad */
/* @var $searchModel Ad */

$this->title                   = __('Manage Advertises');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="button-panel">
    <a href="<?= Url::to(['adv/edit']) ?>" class='btn btn-fab btn-raised btn-primary' data-pjax='0'>
        <i class="fa fa-plus"></i>
    </a>
</div>

<div class="user-index">
    <?php Pjax::begin(['id' => 'page-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
    <div class="panel panel-default data-grid">
        <div class="panel-heading">
            <div class="row" id="data-grid-filters">
                <?php $form = ActiveForm::begin(); ?>
                <div class="col col-md-6">
                </div>
                <div class="col col-md-6">
                    <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Title')])->label(false) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
        <?= GridView::widget([
                                 'id'           => 'data-grid',
                                 'dataProvider' => $dataProvider,
                                 'columns'      => [
                                     [
                                         'attribute' => 'title',
                                         'format'    => 'raw',
                                         'value'     => function ($data) {
                                             return Html::a($data->title, ['adv/edit', 'id' => $data->id], ['data-pjax' => 0]);
                                         },
                                     ],
                                     [
                                         'attribute' => 'views',
                                         'format'    => 'integer',
                                         'value'     => function (Ad $ad) {
                                             return $ad->views;
                                         },
                                     ],
                                     [
                                         'attribute' => 'clicks',
                                         'format'    => 'integer',
                                         'value'     => function (Ad $ad) {
                                             return $ad->clicks;
                                         },
                                     ],
                                     [
                                         'attribute' => 'limit_click',
                                         'format'    => 'integer',
                                         'value'     => function (Ad $ad) {
                                             return $ad->limit_click;
                                         },
                                     ],
                                     [
                                         'attribute' => 'limit_view',
                                         'format'    => 'integer',
                                         'value'     => function (Ad $ad) {
                                             return $ad->limit_view;
                                         },
                                     ],
                                     [
                                         'attribute' => 'status',
                                         'format'    => 'raw',
                                         'value'     => function (Ad $ad) {
                                             return $ad->getStatusLabel();
                                         },
                                     ],
                                     [
                                         'attribute' => 'created_at',
                                         'format'    => 'raw',
                                         'value'     => function ($data) {
                                             return Yii::$app->formatter->asDatetime($data->created_at->getTimestamp());
                                         },
                                     ],
                                     [
                                         'attribute' => 'updated_at',
                                         'format'    => 'raw',
                                         'value'     => function ($data) {
                                             return Yii::$app->formatter->asDatetime($data->updated_at->getTimestamp());
                                         },
                                     ],
                                 ],
                             ]); ?>
    </div>
    <?php Pjax::end() ?>
</div>
