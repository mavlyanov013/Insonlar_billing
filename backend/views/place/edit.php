<?php

use common\models\Ad;
use yii\grid\GridView;
use yii\helpers\Html;
use common\models\Place;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;

/* @var $this                   yii\web\View */
/* @var $model                  Place */
/* @var $dataProvider           \yii\data\ActiveDataProvider */

$this->title                   = $model->isNewRecord ? __('Create place') : $model->title;
$this->params['breadcrumbs'][] = ['url' => ['place/index'], 'label' => __('Manage Places')];
$this->params['breadcrumbs'][] = $this->title;
$user                          = $this->context->_user();

$this->registerJs("
    $(document).on('click', '#cancel-btn', function(){
        $('#modal_ads').modal('hide');
        $('#add_ad_grid').empty();
        $.pjax.reload({container: '#place-ads-grid'});
    });
");
?>
<?php Pjax::begin(['id' => 'place-ads-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<div class="user-create">
    <div class="user-form">
        <?php $form = ActiveForm::begin(['enableAjaxValidation' => true]); ?>
        <div class="row">
            <div class="col col-md-9">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h4><?= __('Place Information') ?></h4>
                    </div>
                    <div class="panel-body">
                        <?= $form->field($model, 'title')
                                 ->textInput(['maxlength' => true])->label() ?>
                        <?= $form->field($model, 'slug')->textInput(['placeholder' => __('Place ID'), 'disabled' => !$model->isNewRecord])->label(false) ?>

                    </div>
                </div>
                <?php if (!$model->isNewRecord): ?>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="pull-left">
                                <h4><?= __('Place Advertises') ?></h4>
                            </div>
                            <div class="pull-right">
                                <a href="#" onclick="addAd()"
                                   class="btn btn-success mr15"><?= __('Add') ?></a>
                                <a href="#" onclick="removeAds()"
                                   class="btn btn-danger"><?= __('Remove') ?></a>
                            </div>
                        </div>
                        <?= GridView::widget([
                                                 'dataProvider' => $dataProvider,
                                                 'id'           => 'place-ads-grid-list',
                                                 'layout'       => "{items}\n<div class='panel-footer'>{pager}<div class='clearfix'></div></div>",
                                                 'tableOptions' => ['class' => 'table table-striped table-hover '],
                                                 'columns'      => [
                                                     ['class' => 'yii\grid\CheckboxColumn'],
                                                     [
                                                         'attribute' => 'title',
                                                         'format'    => 'raw',
                                                         'value'     => function ($model) {
                                                             return Html::a($model->title, ['adv/edit', 'id' => $model->getId()], ['data-pjax' => 0]);
                                                         },
                                                     ],
                                                     [
                                                         'attribute' => 'views',
                                                         'format'    => 'integer',
                                                         'value'     => function (Ad $ad) {
                                                             return $ad->views;
                                                         },
                                                     ],
                                                     [
                                                         'attribute' => 'clicks',
                                                         'format'    => 'integer',
                                                         'value'     => function (Ad $ad) {
                                                             return $ad->clicks;
                                                         },
                                                     ],
                                                     [
                                                         'attribute' => 'status',
                                                         'format'    => 'raw',
                                                         'value'     => function (Ad $ad) {
                                                             return $ad->getStatusLabel();
                                                         },
                                                     ],
                                                     [
                                                         'attribute' => 'created_at',
                                                         'format'    => 'raw',
                                                         'value'     => function ($data) {
                                                             return Yii::$app->formatter->asDatetime($data->created_at->getTimestamp(   ));
                                                         },
                                                     ],
                                                     [
                                                         'attribute' => '_ads_percent',
                                                         'header'    => __('Weight'),
                                                         'format'    => 'raw',
                                                         'options'   => ['style' => 'width:130px'],
                                                         'value'     => function (Ad $ad) use ($model) {
                                                             $urlPlus  = Html::a("<i class='fa fa-plus'></i>", ['place/edit', 'id' => $model->getId(), 'ad' => $ad->getId(), 'percent' => '1'], []);
                                                             $urlMinus = Html::a("<i class='fa fa-minus'></i>", ['place/edit', 'id' => $model->getId(), 'ad' => $ad->getId(), 'percent' => '-1'], []);
                                                             return "<span class='show_hv'>$urlPlus</span>" . $model->getAddPercent($ad) . "<span class='show_hv'>$urlMinus</span>";
                                                         },
                                                     ],
                                                 ],
                                             ]); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col col-md-3 place_settings">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4><?= __('Settings') ?></h4>
                    </div>
                    <div class="panel-body">
                        <?= $form->field($model, 'mode')->widget(ChosenSelect::className(), [
                            'items'         => Place::getModeArray(),
                            'pluginOptions' => [
                                'width'                 => '100%',
                                'allow_single_deselect' => true,
                                'disable_search'        => true,
                            ],
                        ]) ?>

                        <?= $form->field($model, 'status')->widget(ChosenSelect::className(), [
                            'items'         => Place::getStatusArray(),
                            'pluginOptions' => [
                                'width'                 => '100%',
                                'allow_single_deselect' => true,
                                'disable_search'        => true,
                            ],
                        ]) ?>
                    </div>
                    <div class="panel-footer">
                        <div class="text-right">
                            <?= Html::submitButton(__('Save'), ['class' => 'btn btn-primary']) ?>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<?php Pjax::end() ?>

<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" id="modal_ads">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×
                </button>
                <h4 class="modal-title" id="myLargeModalLabel"><?= __('Choose ads') ?></h4>
            </div>
            <div id="add_ad_grid">
            </div>
            <div class="modal-footer text-right">
                <button typeof="button" data-dismiss="modal"
                        class="btn btn-default"><?= __('Close') ?></button>
                <button typeof="button" onclick="addSelectedAds()"
                        class="btn btn-primary"><?= __('Add') ?></button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function addAd() {
        $('#add_ad_grid').load('<?=Url::to(['place/edit', 'id' => $model->getId(), 'list' => 1])?>', function () {
            $('#modal_ads').modal('show');
        })
    }

    function removeAds() {
        var checked = [];
        $('#place-ads-grid table input[name="selection[]"]:checked').each(function (index, element) {
            checked.push(element.value);
        });

        if (checked.length > 0 && confirm('<?=htmlentities(__("Are you sure to remove items"))?>')) {
            var data = {};
            data.data = JSON.stringify(checked);
            data._csrf = $('input[name="_csrf"]').val();
            $.post('<?=Url::to(['place/edit', 'id' => $model->getId(), 'remove' => 1])?>', data, function (response) {
                $.pjax.reload({container: '#place-ads-grid', async: false});
            })
        }
        return false;
    }

    function addSelectedAds() {
        var checked = [];
        $('#add_ad_grid table input[name="selection[]"]:checked').each(function (index, element) {
            checked.push(element.value);
        });

        if (checked.length > 0) {
            var data = {};
            data.data = JSON.stringify(checked);
            data._csrf = $('input[name="_csrf"]').val();
            $.post('<?=Url::to(['place/edit', 'id' => $model->getId(), 'add' => 1])?>', data, function () {
                $('#add_ad_grid').load('<?=Url::to(['place/edit', 'id' => $model->getId(), 'list' => 1])?>', function () {
                });
                $.pjax.reload({container: '#place-ads-grid'});
            })
        }
    }
</script>