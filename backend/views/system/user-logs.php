<?php

use common\components\SystemLog;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel SystemLog */

$this->title                   = __('User Logs');
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'review-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-6 col-md-6">
            </div>
            <div class="col col-md-6 col-md-6">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => $searchModel->getAttributeLabel('search')])->label(false) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?= GridView::widget([
                             'dataProvider' => $dataProvider,
                             'id'           => 'data-grid',
                             'layout'       => "{items}\n<div class='panel-footer'>{summary}{pager}<div class='clearfix'></div></div>",
                             'tableOptions' => ['class' => 'table table-striped table-hover '],
                             'columns'      => [
                                 [
                                     'attribute' => 'name',
                                     'format'    => 'raw',
                                     'value'     => function ($model) {
                                         return $model->name . "<br>" . $model->_user;
                                     },
                                 ],

                                 [
                                     'attribute' => 'ip',
                                     'format'    => 'raw',
                                     'value'     => function ($model) {
                                         return $model->ip . "<br>" . $model->ga;
                                     },
                                 ],
                                 [
                                     'attribute' => 'url',
                                     'format'    => 'raw',
                                     'value'     => function ($model) {
                                         $r = $model->url . "<br><span class='text-sm'>" . $model->get . "</span>";
                                         return Html::a($r, ['system/user-logs-view', 'id' => $model->id], ['data-pjax' => 0, 'target' => '_blank']);
                                     },
                                 ],
                                 [
                                     'attribute' => 'created_at',
                                     'value'     => function ($model) {
                                         return Yii::$app->formatter->asDatetime($model->created_at->getTimestamp());
                                     },
                                 ],
                             ],
                         ]); ?>
</div>
<?php Pjax::end() ?>
