<?php
use common\models\Admin;
use common\components\Config;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Admin */

$this->title = __('System Configuration');
$this->params['breadcrumbs'][] = ['url' => ['/backend/system/translation'], 'label' => __('System')];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="system-configuration">
    <div class="configuration-form">
        <?php $form = ActiveForm::begin([
            'enableAjaxValidation' => true,
        ]); ?>
        <div class="row">
            <div class="col col-md-3"></div>
            <div class="col col-md-6">
                <div class="panel">
                    <div class="panel-body">
                        <?php foreach (Config::getAllConfiguration() as $groupName => $items): ?>
                            <h4><?= $groupName ?></h4>
                            <hr>
                            <?php foreach ($items as $item): ?>
                                <?php echo $this->render('configuration/' . $item['type'], ['item' => $item]) ?>
                            <?php endforeach; ?>
                            <br>
                        <?php endforeach; ?>
                    </div>
                    <div class="panel-footer">
                        <div class=" pull-right">
                            <?= Html::submitButton(__('Update'), ['class' => 'btn btn-success']) ?>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <div class="col col-md-3"></div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<?php
$this->registerJs('

')

?>

