<?php

/* @var $model common\models\Category */

use common\models\Post;
use yii\bootstrap\ActiveForm;
use yii\grid\GridView;
use yii\widgets\Pjax;

$provider     = new Post(['scenario' => 'search']);
$dataProvider = $provider->getProductAddCandidatesForCategory($model, Yii::$app->request->get());
?>

<?php Pjax::begin(['id' => 'content-add-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
    <div class="row" id="add-grid-filters">
        <?php $form = ActiveForm::begin(['id' => '__form-add-search']); ?>
        <div class="col col-md-12">
            <?= $form->field($provider, 'search')->textInput(['placeholder' => $provider->getAttributeLabel('search')])->label(false) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
<?= GridView::widget([
                         'dataProvider' => $dataProvider,
                         'id'           => 'add-grid',
                         'layout'       => "{items}\n {pager}",
                         'tableOptions' => ['class' => 'table table-striped table-hover '],
                         'columns'      => [
                             [
                                 'class' => 'yii\grid\CheckboxColumn',
                             ],
                             [
                                 'attribute' => 'name',
                             ],
                             [
                                 'attribute' => '_type',
                             ],
                             [
                                 'attribute' => 'updated_at',
                                 'format'    => 'raw',
                                 'value'     => function ($data) {
                                     return $data->updated_at ? Yii::$app->formatter->asDatetime($data->updated_at->sec) : '';
                                 },
                             ],
                         ],
                     ]); ?>
<?php Pjax::end() ?>