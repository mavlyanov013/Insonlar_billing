<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

use backend\components\View;
use backend\widgets\checkbo\CheckBo;
use common\widgets\Alert;
use keygenqt\autocompleteAjax\AutocompleteAjax;
use marqu3s\summernote\Summernote;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model \common\models\Tag */
/* @var $searchModel \common\models\Tag */

$this->title                   = __('Manage Tags');
$this->params['breadcrumbs'][] = $this->title;
$user                          = $this->context->_user();

?>

<?php Pjax::begin(['id' => 'tag-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<?= Alert::widget() ?>
<div class="post-form">
    <div class="row">
        <div class="col col-md-5" id="tag_settings">
            <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'action' => Url::current(['id' => $model->getId(), 'save' => 1]), 'options' => ['data-pjax' => 1]]); ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="pull-left">
                        <div class="h4 mb0"><?= __($model->isNewRecord ? 'Add new tag' : 'Edit tag') ?></div>
                    </div>
                    <div class="pull-right">
                        <a href="<?= Url::to(['tag/index']) ?>" class="btn btn-success"><i
                                class="fa fa-plus"></i> <?= __('Add') ?></a>
                    </div>
                </div>
                <div class="panel-body">
                    <?= $form->field($model, 'name_uz')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($model, 'name_cy')->textInput(['maxlength' => true, 'placeholder' => __('Will be generated automatically')]) ?>
                    <?= $form->field($model, 'name_ru')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($model, 'slug')->textInput(['maxlength' => true, 'placeholder' => __('Will be generated automatically')])->label() ?>
                </div>
                <div class="panel-footer">
                    <div class="text-right">
                        <?php if ($model->getId()): ?>
                            <?= Html::a(__('Delete'), ['tag/delete', 'id' => $model->getId()], ['class' => 'btn btn-danger btn-delete', 'data-confirm' => __('Are you sure to delete?')]) ?>
                        <?php endif; ?>
                        <?= Html::submitButton(__('Save'), ['class' => 'btn btn-primary']) ?>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
        <div class="col col-md-7 tags_list">
            <?= $this->renderFile('@backend/views/tag/_list.php', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]) ?>
        </div>
    </div>
</div>
<script>
    function changeAttribute(id, att) {
        var data = {};
        data.id = id;
        data.attribute = att;
        $.get('<?= Url::to(['tag/change'])?>', data)
    }
</script>
<?php

$this->registerJs('
$(\'#tag-name_uz\').blur(function () {
    if ($(\'#tag-slug\').val().length < 2)$(\'#tag-slug\').val(convertToSlug($(this).val()));
}).keyup(function () {
    $(\'#tag-name_cy\').val(convertToCyrill($(this).val()));
});
$(\'#tag-name_cy\').keyup(function () {
    if ($(\'#tag-name_uz\').val().length < 2)$(\'#tag-name_uz\').val(convertToLatin($(this).val()));
})
$(document).ready(function () {
    jQuery(\'#tag_settings\').theiaStickySidebar({
        additionalMarginTop: 70,
        additionalMarginBottom: 20
    });
    
})
');
?>
<?php Pjax::end() ?>
