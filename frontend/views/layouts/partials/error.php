<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

use frontend\models\ErrorForm;
use himiklab\yii2\recaptcha\ReCaptcha;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * Created by PhpStorm.
 * Date: 12/23/17
 * Time: 3:30 PM
 * @var $model ErrorForm
 */
if (!isset($model))
    $model = new ErrorForm();
?>
<div class="popup popup-error" id="popup_error">
    <div class="popup__container" style="max-width: 450px;">
        <div class="popup__content">
            <div class="popup__header">
                <h2><?= __('Xatolik haqida xabar') ?></h2>
                <a href="javascript:void(0)" class="popup-close-btn"><i class="icon popup-close-icon"></i></a>
            </div>
            <div class="popup__body">
                <?php $form = ActiveForm::begin([
                                                    'action'               => Url::to(['site/typo']),
                                                    'errorCssClass'        => 'has_error',
                                                    'successCssClass'      => 'has_success',
                                                    'validateOnSubmit'     => false,
                                                    'enableAjaxValidation' => false,
                                                    'options'              => [
                                                        'id'        => 'error-pjax-form',
                                                        'data-pjax' => true,
                                                    ],
                                                ]); ?>

                <?= $form->field($model, 'text',[
                    'template' => "{label}{input}",
                    'options'  => ['class' => 'form-row'],
                ])->textInput(['readonly' => 'readonly'])->label(false) ?>

                <?= $form->field($model, 'url')->hiddenInput(['value' => Yii::$app->request->referrer])->label(false) ?>

                <?= $form->field($model, 'message', [
                    'template' => "{label}{input}",
                    'options'  => ['class' => 'form-row'],
                ])->textarea(['rows' => 4, 'autofocus' => false, 'autocomplate' => false, 'placeholder' => __('Xabar matni')])->label(false) ?>

                <?= $form->field($model, 'reCaptcha')->widget(ReCaptcha::className(), [])->label(false) ?>

                <div class="hr smaller"></div>
                <p class="form-group">
                    <?= Html::submitButton(__('Yuborish'), ['class' => 'btn rounded']) ?>
                </p>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

