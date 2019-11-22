<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

use backend\widgets\GridView;
use common\models\Partner;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model Partner */
/* @var $searchModel Partner */

$this->title                   = __('Manage Partners');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="button-panel">
    <a href="<?= Url::to(['partner/edit']) ?>" class='btn btn-fab btn-raised btn-primary' data-pjax='0'>
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
                    'value'     => function (Partner $data) {
                        return Html::a($data->name, ['partner/edit', 'id' => $data->id], ['data-pjax' => 0]);
                    },
                ],
                'position',
                [
                    'attribute' => 'status',
                    'format'    => 'raw',
                    'value'     => function (Partner $ad) {
                        return $ad->getStatusLabel();
                    },
                ],
                [
                    'attribute' => 'created_at',
                    'format'    => 'raw',
                    'value'     => function (Partner $data) {
                        return Yii::$app->formatter->asDatetime($data->getTimeSeconds('created_at'));
                    },
                ],
            ],
        ]); ?>
    </div>
    <?php Pjax::end() ?>
</div>
