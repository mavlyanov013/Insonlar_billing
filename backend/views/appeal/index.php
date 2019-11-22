<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

use backend\widgets\GridView;
use common\models\Ad;
use common\models\Appeal;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model Appeal */
/* @var $searchModel Appeal */

$this->title                   = __('Manage Appeals');
$this->params['breadcrumbs'][] = $this->title;
?>
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
                                         'attribute' => 'number',
                                         'format'    => 'raw',
                                         'value'     => function ($data) {
                                             return Html::a($data->number, ['appeal/view', 'id' => $data->id], ['data-pjax' => 0]);
                                         },
                                     ],
                                     [
                                         'attribute' => 'fullname',
                                         'format'    => 'raw',
                                         'value'     => function (Appeal $data) {
                                             return $data->fullname;
                                         },
                                     ],
                                     [
                                         'attribute' => 'status',
                                         'format'    => 'raw',
                                         'value'     => function (Appeal $data) {
                                             return $data->getStatusLabel();
                                         },
                                     ],
                                     [
                                         'attribute' => 'updated_at',
                                         'format'    => 'raw',
                                         'value'     => function (Appeal $data) {
                                             return Yii::$app->formatter->asDatetime($data->getTimeSeconds('updated_at'));
                                         },
                                     ],
                                     [
                                         'attribute' => 'created_at',
                                         'format'    => 'raw',
                                         'value'     => function (Appeal $data) {
                                             return Yii::$app->formatter->asDatetime($data->getTimeSeconds('created_at'));
                                         },
                                     ],
                                 ],
                             ]); ?>
    </div>
    <?php Pjax::end() ?>
</div>
