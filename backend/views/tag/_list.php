<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

use backend\components\View;
use backend\models\TagSearch;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model \common\models\Tag */
/* @var $searchModel \common\models\Tag */

?>
<div class="panel panel-default data-grid">
    <div class="panel-heading" id="data-grid-filters">
        <?php $form = ActiveForm::begin(); ?>
        <?= $form->field($searchModel, 'search', ['options' => ['class' => false], 'labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name'), ['class' => 'tag-search']])->label(false) ?>
        <?php ActiveForm::end(); ?>
    </div>
    <?= GridView::widget([
                             'id'           => 'data-grid',
                             'dataProvider' => $dataProvider,
                             'columns'      => [
                                 [
                                     'attribute' => 'name',
                                     'format'    => 'raw',
                                     'value'     => function ($data) {
                                         return Html::a($data->name, Url::current(['id' => $data->getId()]));
                                     },
                                 ],

                                 [
                                     'attribute' => 'count',
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
