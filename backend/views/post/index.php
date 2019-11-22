<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\components\Config;
use common\models\Post;
use MongoDB\BSON\Timestamp;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;

/* @var $this backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model \common\models\Post */
/* @var $searchModel \common\models\Post */

$this->title                   = __('Manage Posts');
$this->params['breadcrumbs'][] = $this->title;

$hasAccess = $this->_user()->canAccessToResource('post-view/index')
?>
<div class="button-panel">
    <?= Html::a('<i class="fa fa-file-text"></i>', ['/post/create', 'type' => Post::TYPE_NEWS], [
        'data-pjax' => false, 'title' => __('Create {type} Post', ['type' => Post::TYPE_NEWS,]), 'class' => 'btn btn-fab btn-raised btn-primary',
    ]) ?>
    <?= Html::a('<i class="fa fa-image"></i>', ['/post/create', 'type' => Post::TYPE_GALLERY], [
        'data-pjax' => false, 'title' => __('Create {type} Post', ['type' => Post::TYPE_GALLERY]), 'class' => 'btn btn-fab btn-raised btn-primary',
    ]) ?>
    <?= Html::a('<i class="fa fa-film"></i>', ['/post/create', 'type' => Post::TYPE_VIDEO], [
        'data-pjax' => false, 'title' => __('Create {type} Post', ['type' => Post::TYPE_VIDEO]), 'class' => 'btn btn-fab btn-raised btn-primary',
    ]) ?>
</div>

<?php Pjax::begin(['id' => 'post-grid', 'options' => ['data-pjax' => false], 'timeout' => false, 'enablePushState' => false]) ?>
<div class="panel panel-default data-grid">
    <div class="panel-heading">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>

            <div class="col col-md-3  col-md-offset-3">

            </div>
            <div class="col col-md-6 col-md-6">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Title / Slug')])->label(false) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?= GridView::widget([
                             'id'           => 'data-grid',
                             'dataProvider' => $dataProvider,
                             'rowOptions'   => function ($model, $key, $index, $grid) use ($searchModel) {
                                 $class = $model->status == Post::STATUS_DRAFT && $searchModel->status != Post::STATUS_DRAFT ? 'text-muted ' : ' ';
                                 $class .= $model->label == Post::LABEL_IMPORTANT ? 'text-bold ' : ' ';
                                 return [
                                     'class' => $class,
                                 ];
                             },
                             'columns'      => [
                                 [
                                     'attribute' => 'title',
                                     'format'    => 'raw',
                                     'value'     => function (Post $data) {
                                         return Html::a($data->getTitleView(), ['post/edit', 'id' => $data->id], ['data-pjax' => 0]);
                                     },
                                 ],


                                 [
                                     'attribute' => 'views',
                                     'format'    => 'raw',
                                     'value'     => function ($data) use ($hasAccess) {
                                         $view = $data->views;

                                         return $view;
                                     },
                                 ],
                                 [
                                     'attribute' => 'status',
                                     'format'    => 'raw',
                                     'value'     => function ($data) {
                                         return $data->getStatusLabel();
                                     },
                                 ],
                                 $searchModel->status == Post::STATUS_DRAFT ?
                                     [
                                         'attribute' => 'created_at',
                                         'format'    => 'raw',
                                         'value'     => function ($data) {
                                             return ($data->created_at instanceof Timestamp) ? Yii::$app->formatter->asDatetime($data->created_at->getTimestamp()) : '';
                                         },
                                     ] :
                                     [
                                         'attribute' => 'published_on',
                                         'format'    => 'raw',
                                         'value'     => function ($data) {
                                             return ($data->published_on instanceof Timestamp) ? Yii::$app->formatter->asDatetime($data->published_on->getTimestamp()) : '';
                                         },
                                     ],
                                 [
                                     'attribute' => 'updated_at',
                                     'format'    => 'raw',
                                     'value'     => function ($data) {
                                         return Yii::$app->formatter->asDatetime($data->updated_at->getTimestamp());
                                     },
                                 ],
                                 $searchModel->status == Post::STATUS_DRAFT && $this->_user()->canAccessToResource('post/trash') ?
                                     [
                                         'format' => 'raw',
                                         'value'  => function ($data) {
                                             if ($data->status == Post::STATUS_DRAFT) {
                                                 return Html::a(__('Delete'), ['post/trash', 'id' => $data->id], ['class' => 'btn-delete']);
                                             }
                                         },
                                     ] :
                                     [
                                         'attribute' => 'is_main',
                                         'format'    => 'raw',
                                         'value'     => function ($data) {
                                             return CheckBo::widget(['type' => 'switch', 'options' => ['onclick' => "changeAttribute('$data->id', 'is_main')", 'disabled' => $data->status != Post::STATUS_PUBLISHED ? true : false], 'name' => $data->id, 'value' => $data->is_main]);
                                         },
                                     ],
                             ],
                         ]); ?>
</div>
<script>
    function changeAttribute(id, att) {
        var data = {};
        data.id = id;
        data.attribute = att;
        $.get('<?= Url::to(['post/change'])?>', data)
    }
</script>
<?php Pjax::end() ?>
