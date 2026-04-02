<?php

use backend\components\CommonHelper;
use backend\widgets\GridView;
use common\models\payment\Payment;
use kartik\daterange\DateRangePicker;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;

/**
 * @var $this         backend\components\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel  Payment
 */

$this->title                   = __('Manage Payments');
$this->params['breadcrumbs'][] = $this->title;
$this->registerJs("
    var ranges={
        '" . addslashes(__('Today')) . "': [moment(), moment()],
        '" . addslashes(__('Yesterday')) . "': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        '" . addslashes(__('Last 7 days')) . "': [moment().subtract(6, 'days'), moment()],
        '" . addslashes(__('Last 30 days')) . "': [moment().subtract(29, 'days'), moment()],
        '" . addslashes(__('This Month')) . "': [moment().startOf('month'), moment().endOf('month')],
        '" . addslashes(__('Last Month')) . "': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
    };
", \yii\web\View::POS_HEAD);
?>
<?php if (false): ?>
    <div class="button-panel">
        <?= Html::a('<i class="fa fa-file-text"></i>', ['/backend/payment/transfer'], [
            'data-pjax' => false, 'title' => __('Create Transfer', []), 'class' => 'btn btn-fab btn-raised btn-primary',
        ]) ?>
        <?= Html::a('<i class="fa fa-money"></i>', ['/backend/payment/cash'], [
            'data-pjax' => false, 'title' => __('Create Cash', []), 'class' => 'btn btn-fab btn-raised btn-primary',
        ]) ?>
    </div>
<?php endif; ?>

<div class="panel mb25">
    <?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="row" id="data-grid-filters">
                <?php $form = ActiveForm::begin(); ?>
                <div class="col col-sm-3 col-md-4">
                    <?= DateRangePicker::widget([
                        'model'         => $searchModel,
                        'attribute'     => 'datetime_range',
                        'convertFormat' => true,
                        'pluginOptions' => [
                            'timePicker'          => false,
                            'timePickerIncrement' => 30,
                            'ranges'              => new \yii\web\JsExpression('ranges'),
                            'locale'              => \common\components\Config::getDateRangeLocale(),
                        ],
                    ]);

                    ?>
                </div>
                <div class="col col-sm-3 col-md-2">
                    <?= $form->field($searchModel, 'method', ['labelOptions' => ['class' => 'invisible']])->widget(ChosenSelect::className(), [
                        'items'         => array_merge([''], Payment::getMethodOptions()),
                        'options'       => [
                            'data-placeholder' => __('Payment Method'),
                        ],
                        'pluginOptions' => ['width' => '100%', 'allow_single_deselect' => true, 'disable_search' => true],
                    ])->label(false) ?>
                </div>
                <div class="col col-sm-3 col-md-2">
                    <?= $form->field($searchModel, 'status', ['labelOptions' => ['class' => 'invisible']])->widget(ChosenSelect::className(), [
                        'items'         => array_merge([''], Payment::getStatusOptions()),
                        'options'       => [
                            'data-placeholder' => __('Payment Status'),
                        ],
                        'pluginOptions' => ['width' => '100%', 'allow_single_deselect' => true, 'disable_search' => true],
                    ])->label(false) ?>
                </div>

                <div class="col col-sm-2 col-md-3">
                    <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => $searchModel->getAttributeLabel('search')])->label(false) ?>
                </div>
                <?php if ($this->_user()->canAccessToResource('payment/download')): ?>
                    <div class="col col-sm-1 col-md-1">
                        <a href="<?= Url::to(array_merge(['/backend/payment/download'], Yii::$app->request->get())) ?>"
                           class="btn btn-default btn-block"
                           target="_blank" data-pjax="0"><i class="fa fa-file-excel-o"></i> </a>
                    </div>
                <?php endif; ?>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
        <?= GridView::widget([
            'id'           => 'data-grid',
            'dataProvider' => $dataProvider,
            'showFooter'   => true,
            'columns'      => [
                [
                    'attribute' => 'user_data',
                    'format'    => 'raw',
                    'value'     => function ($data) {
                        return Html::a($data->user_data ? $data->user_data : $data->transaction_id, [
                            '/backend/payment/update',
                            'id' => $data->id,
                        ], ['data-pjax' => 0]);
                    },
                ],
                [
                    'attribute' => 'amount',
                    'format'    => 'integer',
                    'footer'    => "<strong>" . CommonHelper::pageTotal($dataProvider->models, 'amount') . "</strong>",
                ],
                [
                    'attribute' => 'method',
                    'format'    => 'raw',
                    'value'     => function ($model) {
                        /* @var $model Payment */
                        return $model->getMethodLabel();
                    },
                ],
                [
                    'attribute' => 'status',
                    'format'    => 'raw',
                    'value'     => function ($model) {
                        /* @var $model Payment */
                        return $model->getStatusLabel();
                    },
                ],
                [
                    'attribute' => 'time',
                    'format'    => 'raw',
                    'value'     => function ($model) {
                        /* @var $model Payment */
                        return $model->getPaymentDateFormatted();
                    },
                ],
                'transaction_id',
            ],
        ]); ?>
    </div>
    <?php Pjax::end() ?>
</div>
