<?php

use backend\components\View;
use backend\widgets\checkbo\CheckBo;
use common\widgets\Alert;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this View */
/* @var $model common\models\Category */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title                   = __('Manage Categories');
$this->params['breadcrumbs'][] = $this->title;
?>
<?php Pjax::begin(['id' => 'category-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<?= Alert::widget() ?>
<div class="row">
    <div class="col col-md-5">
        <?= $this->renderFile('@backend/views/category/_list.php', ['model' => $model]); ?>
    </div>
    <div class="col col-md-7" id="main_panel">
        <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'options' => ['data-pjax' => 1]]); ?>
        <div class="box-tab">
            <ul class="nav nav-tabs">
                <li class="active">
                    <a data-toggle="tab" href="#tab_general" aria-expanded="true">
                        <?= __('General') ?>
                    </a>
                </li>
                <li>
                    <a data-toggle="tab" href="#tab_display">
                        <?= __('Display') ?>
                    </a>
                </li>
            </ul>
            <div class="tab-content">
                <div id="tab_general" class="tab-pane active">
                    <div class="row">
                        <div class="col col-md-12">
                            <?= $form->field($model, 'name')->textInput(['maxlength' => true,])->label() ?>
                            <?= $form->field($model, 'slug')->textInput(['maxlength' => true,])->label() ?>
                            <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>
                            <?php foreach ($model->getBooleanAttributes() as $attribute): ?>
                                <?= $form->field($model, $attribute)->widget(CheckBo::className()) ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div id="tab_display" class="tab-pane ">
                    <?= $form->field($model, 'page_title')->textInput(['maxlength' => true,])->label() ?>
                    <?= $form->field($model, 'meta_keywords')->textarea(['maxlength' => true,])->label() ?>
                    <?= $form->field($model, 'meta_description')->textarea(['maxlength' => true,])->label() ?>
                </div>
            </div>
        </div>
        <div class="text-right">
            <?php if (!$model->isNewRecord && $this->_user()->canAccessToResource('category/delete')): ?>
                <?= Html::a(__('Delete'), ['category/delete', 'id' => $model->getId()], ['class' => 'btn btn-danger btn-delete', 'data-confirm' => __('Are you sure to delete?')]) ?>
            <?php endif; ?>
            <?= Html::submitButton(__('Save'), ['class' => 'btn btn-primary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<?php
$this->registerJs('
    $(\'#category-name\').blur(function () {
        if ($(\'#category-slug\').val().length < 2)$(\'#category-slug\').val(convertToSlug($(this).val()));
    });
    
    $(document).on(\'ready pjax:success\', function () {
        jQuery(\'#main_panel\').theiaStickySidebar({
            additionalMarginTop: 74
        });
    })
');
?>
<?php Pjax::end() ?>


<script type="application/javascript">

    function addProductShow() {
        $('#add_product_grid').load('<?=Url::to(['category/index', 'id' => $model->id, 'choose' => 1])?>', null, function () {
            $('#modal_products').modal('show');
        })
    }

    function removeProducts() {

        var checked = [];

        $('#products-grid table input[name="selection[]"]:checked').each(function (index, element) {
            checked.push(element.value);
        });

        if (checked.length > 0) {
            var data = {};
            data.data = JSON.stringify(checked);
            data._csrf = $('input[name="_csrf"]').val();
            $.post('<?=Url::to(['category/index', 'id' => $model->id, 'remove' => 1])?>', data, function (response) {
                $('#tab_products').load('<?=Url::to(['category/index', 'id' => $model->id, 'products' => 1])?>');
            })
        }
        return false;
    }

    function addSelectedProducts() {
        var checked = [];

        $('#add_product_grid table input[name="selection[]"]:checked').each(function (index, element) {
            checked.push(element.value);
        });

        if (checked.length > 0) {
            var data = {};
            data.data = JSON.stringify(checked);
            data._csrf = $('input[name="_csrf"]').val();
            $.post('<?=Url::to(['category/index', 'id' => $model->id, 'add' => 1])?>', data, function () {
                $('#add_product_grid').load('<?=Url::to(['category/index', 'id' => $model->id, 'choose' => 1])?>');
                $('#tab_products').load('<?=Url::to(['category/index', 'id' => $model->id, 'products' => 1])?>');
            })
        }
    }

</script>
<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" id="modal_products">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                        aria-hidden="true">×
                </button>
                <h4 class="modal-title" id="myLargeModalLabel"><?= __('Add Products') ?></h4>
            </div>
            <div class="modal-body" id="add_product_grid">
            </div>
            <div class="modal-footer text-right">
                <button typeof="button" data-dismiss="modal" class="btn btn-default"><?= __('Close') ?></button>
                <button typeof="button" onclick="addSelectedProducts()"
                        class="btn btn-primary"><?= __('Add') ?></button>
            </div>
        </div>
    </div>
</div>

