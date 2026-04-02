<?php

use common\models\Login;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = __('Login History');

?>
<ul class="breadcrumb">
    <li><a href="/"><?= __('Home') ?></a></li>
    <li><a href="/backend/system/translation"><?= __('System') ?></a></li>
    <li class="active"><?= $this->title ?></li>
</ul>
<div class="dashboard-index">
    <?php Pjax::begin(['id' => 'log-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
    <div class="panel panel-default data-grid">
        <div class="panel-heading">
            <div class="row" id="data-grid-filters">
                <?php $form = ActiveForm::begin(); ?>
                <div class="col col-md-6 col-md-2"></div>
                <div class="col col-md-6 col-md-6">
                    <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['autofocus' => true, 'placeholder' => $searchModel->getAttributeLabel('search')])->label(false) ?>
                </div>
                <div class="col col-md-6 col-md-2">
                    <?= $form->field($searchModel, 'status')->dropDownList(Login::getStatusOptions())->label(false) ?>
                </div>

                <div class="col col-md-6 col-md-2">
                    <?= $form->field($searchModel, 'type')->dropDownList(Login::getIsAdminOptions())->label(false) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
        <?= GridView::widget([
                                 'dataProvider' => $dataProvider,
                                 'id'           => 'data-grid',
                                 'layout'       => "{items}\n<div class='panel-footer'>{pager}<div class='clearfix'></div></div>",
                                 'tableOptions' => ['class' => 'table table-striped table-hover'],
                                 'columns'      => [
                                     [
                                         'attribute' => 'id',
                                         'format'    => 'raw',
                                         'value'     => function ($data) {
                                             return Html::a($data->getId(), ['/backend/system/del-login', 'id' => $data->id], ['onclick' => 'return confirm("Are you sure?")']);
                                         },
                                     ],
                                     [
                                         'attribute' => 'ip',
                                     ],
                                     [
                                         'attribute' => 'login',
                                     ],
                                     [
                                         'attribute' => 'status',
                                         'format'    => 'raw',
                                         'value'     => function ($data) {
                                             return $data->getStatusLabel();
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
    </div>
    <?php Pjax::end() ?>
</div>
