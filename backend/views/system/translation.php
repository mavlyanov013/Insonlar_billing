<?php

use backend\components\View;
use common\components\Config;
use common\models\SystemMessage;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;

/* @var $this View */
/* @var $searchModel SystemMessage */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title                   = __('System UI Translations');
$this->params['breadcrumbs'][] = ['url' => ['system/index'], 'label' => __('System')];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php Pjax::begin(['id' => 'translation-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<div class="panel panel-default data-grid">
    <div class="panel-heading">
        <div class="row">
            <?php $form = ActiveForm::begin(['options' => ['id' => "data-grid-filters"]]); ?>
            <div class="col col-md-6 col-md-2">
                <?= $form->field($searchModel, 'language')->widget(ChosenSelect::className(), [
                    'items'         => Config::getLanguageOptions(),
                    'pluginOptions' => ['width' => '100%', 'allow_single_deselect' => true, 'disable_search' => true],
                ])->label(false); ?>
            </div>
            <div class="col col-md-6 col-md-6">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['autofocus' => true, 'placeholder' => $searchModel->getAttributeLabel('search')])->label(false) ?>
            </div>
            <?php ActiveForm::end(); ?>
            <?php if ($this->_user()->canAccessToResource('system/upload-trans')): ?>
                <?php $form = ActiveForm::begin(['action' => ['system/translation'], 'options' => ['id' => 'upload_form', 'method' => 'post', 'enctype' => 'multipart/form-data', 'data-pjax' => false]]); ?>
                <div class="col col-md-6 col-md-4 text-right">
                    <?php if (Config::isLatinCyrill()): ?>
                        <a onclick="return confirm('<?= htmlentities(__('Are you sure to transliterate all messages?')) ?>')"
                           data-pjax="0" href="<?= Url::to(['system/translation', 'convert' => 1]) ?>"
                           class="btn btn-default"><i class='fa fa-refresh'></i></a>
                    <?php endif; ?>
                    <a data-pjax="0" href="<?= Url::to(['system/download']) ?>" class="btn btn-default"><i
                            class='fa fa-download'></i></a>

                    <div class="file-wrapper">
                        <?= $form->field($model, 'file', ['template' => '{input}'])->fileInput(['onchange' => 'if(confirm("' . htmlentities(__('Are your sure upload all translations?')) . '"))$("#upload_form").submit()']) ?>
                        <button type="button" onclick="$('#formuploadtrans-file').click()" class="btn btn-default"><i
                                class='fa fa-upload'></i></button>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            <?php endif; ?>

        </div>
    </div>
    <?= GridView::widget([
                             'dataProvider' => $dataProvider,
                             'id'           => 'data-grid',
                             'layout'       => "{items}\n<div class='panel-footer'>{summary}{pager}<div class='clearfix'></div></div>",
                             'tableOptions' => ['class' => 'table table-striped table-hover'],
                             'columns'      => [
                                 [
                                     'attribute' => 'message',
                                     'format'    => 'raw',
                                     'value'     => function ($data) {
                                         return Html::a($data->message, '#' . $data->message, ['message-id' => $data->id, 'onClick' => "showTranslation('{$data->id}')"]);
                                     },
                                 ],
                                 [
                                     'attribute' => $searchModel->language,
                                 ],
                             ],
                         ]); ?>
</div>
<?php Pjax::end() ?>

<div class="modal fade" id="modal_translation" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span
                        aria-hidden="true">&times;</span><span
                        class="sr-only"><?= __('Close') ?></span></button>
                <h4 class="modal-title" id="myModalLabel"><?= __('Translate Message') ?></h4>
            </div>
            <div class="modal-body" id="translation_body">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger pull-left" id="delete-button"
                        onclick="deleteTranslation()"><?= __('Delete') ?></button>

                <button type="button" class="btn btn-default"
                        onclick="closeTranslation()"><?= __('Close') ?></button>
                <button type="button" class="btn btn-primary"
                        onclick="saveTranslation()"><?= __('Update') ?></button>
            </div>
        </div>
    </div>
</div>

<script type="application/javascript">

    function showTranslation(id) {
        $('#translation_body').load('<?=Url::to(["system/translate"])?>/' + id, function (data) {
            $('#modal_translation').modal('show');
            $('#delete-button').attr('trans-id', id);
        });
        return false;
    }

    function closeTranslation() {
        $('#modal_translation').modal('hide');
        return false;
    }

    function deleteTranslation() {
        if (confirm('<?=addslashes(__("Are you sure to delete message?"))?>')) {
            $.ajax(
                '<?=Url::to(["system/delete"])?>/' + $('#delete-button').attr('trans-id'),
                {
                    method: 'GET',
                    success: function () {
                        $('#modal_translation').modal('hide');
                        $('#data-grid-filters input:first').trigger('change');
                    }
                }
            );
        }
    }
    function saveTranslation() {
        var form = $('#translation-form');
        $.post(form.attr('action'), form.serialize(), function () {
                $('#modal_translation').modal('hide');
                $('#data-grid-filters input:first').trigger('change');
                return false;
            }
        );

        return false;
    }

</script>