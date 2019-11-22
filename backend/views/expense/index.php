<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

use backend\widgets\GridView;
use common\components\Config;
use kartik\daterange\DateRangePicker;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\widgets\Pjax;

/**
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $searchModel  \common\models\Expense
 */

$this->title                   = __('Manage Expenses');
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
<div class="button-panel">
    <a href="<?= linkTo(['edit']) ?>" class='btn btn-fab btn-raised btn-primary' data-pjax='0'>
        <i class="fa fa-plus"></i>
    </a>
</div>

<div class="user-index">
    <?php Pjax::begin(['id' => 'page-grid', 'timeout' => false, 'enablePushState' => false]) ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="row" id="data-grid-filters">
                <?php $form = ActiveForm::begin(); ?>
                <div class="col col-md-6">
                    <div class="form-group">
                        <div class="input-group drp-container">
                            <?= DateRangePicker::widget([
                                'model'          => $searchModel,
                                'attribute'      => 'date_range',
                                'startAttribute' => 'from_date',
                                'endAttribute'   => 'to_date',
                                'useWithAddon'   => true,
                                'convertFormat'  => true,
                                'pluginOptions'  => [
                                    'timePicker'          => false,
                                    'timePickerIncrement' => 30,
                                    'ranges'              => new \yii\web\JsExpression('ranges'),
                                    'locale'              => Config::getDateRangeLocale(),
                                ],
                            ]);

                            ?>
                            <div class="input-group-addon">
                            <span class="input-group-text">
                                <i class="fa fa-calendar"></i>
                            </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col col-md-3"></div>
                <div class="col col-md-6 col-md-6">
                    <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => $searchModel->getAttributeLabel('search')])->label(false) ?>
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
                    'value'     => function ($data) {
                        return Html::a($data->name, ['edit', 'id' => $data->id], ['data-pjax' => 0]);
                    },
                ],
                'amount:currency',
                [
                    'attribute' => '_category',
                    'format'    => 'raw',
                    'value'     => function ($data) {
                        return $data->category ? $data->category->name : '';
                    },
                ],
                [
                    'attribute' => 'created_at',
                    'value'     => function ($data) {
                        return Yii::$app->formatter->asDatetime($data->created_at->getTimestamp());
                    },

                ],
                [
                    'attribute' => 'updated_at',
                    'value'     => function ($data) {
                        return Yii::$app->formatter->asDatetime($data->updated_at->getTimestamp());
                    },

                ],
            ],
        ]); ?>
    </div>
    <?php Pjax::end() ?>
</div>
