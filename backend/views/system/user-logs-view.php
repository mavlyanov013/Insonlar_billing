<?php
/**
 * Created by PhpStorm.
 * User: shavkat
 * Date: 5/31/18
 * Time: 10:10 AM
 * @var $model Log
 */

use backend\widgets\AceEditorWidget;
use common\models\Log;
use yii\widgets\ActiveForm;

$this->title                   = $model->url;
$this->params['breadcrumbs'][] = ['url' => ['/backend/system/user-logs'], 'label' => __('User Logs')];
$this->params['breadcrumbs'][] = $this->title;

$data         = $model->getAttributes();
$data['post'] = @json_decode($data['post']);
$model->ip    = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

?>
<?php $form = ActiveForm::begin(['enableAjaxValidation' => true, 'enableClientValidation' => true, 'validateOnSubmit' => false, 'options' => ['id' => 'post_form']]); ?>

<?= $form->field($model, 'ip')
         ->widget(AceEditorWidget::className(), ['options' => ['id' => 'dbg_content'], 'mode' => 'json', 'containerOptions' => ['style' => 'min-height:1000px']]) ?>
<?php ActiveForm::end(); ?>

