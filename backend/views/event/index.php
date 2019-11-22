<?php

use backend\widgets\GridView;
use common\models\Ad;
use common\models\Event;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model Event */
/* @var $searchModel Event */

$this->title                   = __('Manage Events');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="button-panel">
    <a href="<?= Url::to(['event/edit']) ?>" class='btn btn-fab btn-raised btn-primary' data-pjax='0'>
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
                                         'attribute' => 'name',
                                         'format'    => 'raw',
                                         'value'     => function (Event $data) {
                                             return Html::a($data->name, ['event/edit', 'id' => $data->id], ['data-pjax' => 0]);
                                         },
                                     ],
                                     [
                                         'attribute' => 'status',
                                         'format'    => 'raw',
                                         'value'     => function (Event $ad) {
                                             return $ad->getStatusLabel();
                                         },
                                     ],
                                     [
                                         'attribute' => 'from_date',
                                         'format'    => 'raw',
                                         'value'     => function (Event $data) {
                                             return Yii::$app->formatter->asDatetime($data->getTimeSeconds('from_date'));
                                         },
                                     ],
                                     [
                                         'attribute' => 'to_date',
                                         'format'    => 'raw',
                                         'value'     => function (Event $data) {
                                             return Yii::$app->formatter->asDatetime($data->getTimeSeconds('to_date'));
                                         },
                                     ],
                                     [
                                         'attribute' => 'created_at',
                                         'format'    => 'raw',
                                         'value'     => function (Event $data) {
                                             return Yii::$app->formatter->asDatetime($data->getTimeSeconds('created_at'));
                                         },
                                     ],
                                 ],
                             ]); ?>
    </div>
    <?php Pjax::end() ?>
</div>
