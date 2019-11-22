<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

use common\models\Category;
use common\models\Expense;
use frontend\components\View;
use trntv\filekit\widget\Upload;
use wbraganca\fancytree\FancytreeWidget;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use trntv\yii\datetime\DateTimeWidget;
use common\components\Config;

/* @var $this View */
/* @var $model Expense */


$this->title                   = $model->isNewRecord ? __('Create Expense') : $model->name;
$this->params['breadcrumbs'][] = ['url' => ['expense/index'], 'label' => __('Manage Expenses')];
$this->params['breadcrumbs'][] = $this->title;
$user                          = $this->context->_user();
$this->registerJs('initModule();');
?>
<?php Pjax::begin(['enablePushState' => false]) ?>
<div class="row">
    <?php $form = ActiveForm::begin(['enableAjaxValidation' => false]); ?>
    <div class="col col-md-9">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h4><?= __('Expense Information') ?></h4>
            </div>
            <div class="panel-body">
                <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'comment')->textarea(['rows' => 6])->label(__('Short Information')) ?>

                <?= $form->field($model, 'files')->widget(
                    Upload::className(),
                    [
                        'url'              => ['file-storage/upload'],
                        'acceptFileTypes'  => new JsExpression('/(\.|\/)(jpe?g|png|zip|rar|pdf|tiff|tif|gif|txt|docx?)$/i'),
                        'sortable'         => true,
                        'maxFileSize'      => 20 * 1024 * 1024, // 5 MiB
                        'maxNumberOfFiles' => 20,
                        'clientOptions'    => ['height' => '120%'],
                    ]
                ) ?>
            </div>

        </div>
    </div>
    <div class="col col-md-3 post_settings">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4><?= __('Settings') ?></h4>
            </div>
            <div class="panel-body">
                <?= $form->field($model, 'amount')->textInput(['type' => 'number']) ?>
                <?php
                echo $form->field($model, '_category')->hiddenInput(['id' => '_category'])->label(__('Categories'));
                echo FancytreeWidget::widget([
                    'options' => [
                        'checkbox'   => true,
                        'selectMode' => 1,
                        'source'     => Category::getCategoryTreeAsArray([$model->_category], Config::get(Config::CONFIG_CATALOG_EXPENSE_ROOT)),
                        'extensions' => ['dnd'],
                        'select'     => new JsExpression('function(event, data) {
                                                                                    var selNodes = data.tree.getSelectedNodes();
                                                                                    var selKeys = $.map(selNodes, function(node){
                                                                                            return node.key;
                                                                                        });
                                                                                    $("#_category").val(selKeys.join(","));
                                                                                }'),
                    ],
                ]);
                ?>
                <div id="widget_expense_on_wrapper">
                    <?= $form->field($model, 'expense_on', [
                        'options' => [
                            'value'    => $model->getTimeSeconds('expense_on'),
                            'id'       => 'hidden_expense_on',
                            //'disabled' => !($model->status == Post::STATUS_PUBLISHED || $model->status == Post::STATUS_DRAFT),
                        ],
                    ])->hiddenInput(['id' => 'expense_on_time', 'value' => $model->getTimeSeconds('expense_on'),]) ?>
                    <?php $time = $model->getTimeSeconds('expense_on') ?>
                    <?= DateTimeWidget::widget([
                                                   'id'               => 'widget_expense_on',
                                                   'locale'           => Yii::$app->language == Config::LANGUAGE_UZBEK ? 'uz-latn' : (Yii::$app->language == Config::LANGUAGE_CYRILLIC ? 'uz' : 'ru'),
                                                   'model'            => $model,
                                                   'name'             => 'date_expense_on_time',
                                                   'value'            => $time ? Yii::$app->formatter->asDatetime($time, 'dd.MM.yyyy, HH:mm') : null,
                                                   'containerOptions' => [],
                                                   'clientEvents'     => [
                                                       'dp.change' => new JsExpression('function(d){
                                                                           time = d.date._d.getTime() / 1000;
                                                                           $("#expense_on_time").val(Math.round(time))
                                                                        }'),
                                                   ],
                                               ]) ?>
                </div>
            </div>
            <div class="panel-footer">
                <div class="text-right">
                    <?= Html::submitButton(__('Save'), ['class' => 'btn btn-primary']) ?>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<script type="application/javascript">

    function initModule() {
        $('.post_settings').theiaStickySidebar({
            additionalMarginTop: 70,
            additionalMarginBottom: 20
        });
    }

</script>
<?php Pjax::end(); ?>
