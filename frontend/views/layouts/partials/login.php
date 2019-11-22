<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

use frontend\models\LoginForm;
use yii\authclient\widgets\AuthChoice;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/**
 * Created by PhpStorm.
 * Date: 12/23/17
 * Time: 3:30 PM
 * @var $model LoginForm
 */
?>

<div class="popup popup-registration" id="popup_register">
    <div class="popup__container" style="max-width: 480px;">
        <div class="popup__content">
            <div class="popup__header">
                <h2><?= __('Ro’yhatdan o’tish') ?></h2>

                <a href="javascript:void(0)" class="popup-close-btn"><i class="icon popup-close-icon"></i></a>
            </div><!-- End of popup__header -->

            <div class="popup__body">
                <?php Pjax::begin(['enablePushState' => false, 'timeout' => 10000]) ?>
                <?php $form = ActiveForm::begin([
                                                    'action'               => Url::to(['account/signup']),
                                                    'errorCssClass'        => 'has_error',
                                                    'successCssClass'      => 'has_success',
                                                    'enableAjaxValidation' => true,
                                                    'options'              => ['daata-pjax' => 1],
                                                ]); ?>

                <?= $form->field($regModel, 'fullname', [
                    'template' => "<span class=\"after\"><i class=\"icon form-error-icon\"></i><i class=\"icon form-success-icon\"></i></span>\n{input}",
                    'options'  => ['class' => 'form-row validate'],
                ])->textInput(['autofocus' => false, 'placeholder' => __('Fullname')])->label(false) ?>

                <?= $form->field($regModel, 'email', [
                    'template' => "<span class=\"after\"><i class=\"icon form-error-icon\"></i><i class=\"icon form-success-icon\"></i></span>\n{input}",
                    'options'  => ['class' => 'form-row validate'],
                ])->textInput(['autofocus' => false, 'placeholder' => __('Email')])->label(false) ?>

                <?= $form->field($regModel, 'password', [
                    'template' => "<span class=\"after\"><i class=\"icon form-error-icon\"></i><i class=\"icon form-success-icon\"></i></span>\n{input}",
                    'options'  => ['class' => 'form-row validate'],
                ])->passwordInput(['autofocus' => false, 'placeholder' => __('Password')])->label(false) ?>

                <?= $form->field($regModel, 'confirmation', [
                    'template' => "<span class=\"after\"><i class=\"icon form-error-icon\"></i><i class=\"icon form-success-icon\"></i></span>\n{input}",
                    'options'  => ['class' => 'form-row mb-none  validate'],
                ])->passwordInput(['autofocus' => false, 'placeholder' => __('Confirmation')])->label(false) ?>

                <p class="form-hint"><?= __('Kalit so\'z kamida 6 ta harfdan iborat bo’lishi kerak') ?></p>

                <p class="form-group">
                    <?= Html::submitButton(__('Ro\'yhatdan o\'tish'), ['class' => 'btn rounded']) ?>
                </p>

                <p class="enter-options"><span><?= __('Ijtimoiy tarmoqlar bilan kiring') ?></span></p>

                <?php $authChoice = AuthChoice::begin(['baseAuthUrl' => ['account/auth'], 'popupMode' => true]) ?>
                <p>
                    <?php foreach ($authChoice->getClients() as $i => $client) {
                        echo $authChoice->clientLink($client, $client->getTitle(), ['class' => 'btn rounded ' . $client->getName()]);
                        if ($i + 1 < count($authChoice->getClients())) {
                            echo "<span class='h-space'></span>";
                        }
                    } ?>
                </p>
                <?php AuthChoice::end() ?>

                <div class="hr smaller"></div>

                <p><?= __('Menda akkount bor') ?><span class="h-space"></span>
                    <a href="#" data-target="popup-enter"><?= __('Kirish') ?></a>
                </p>
                <?php ActiveForm::end(); ?>
                <?php Pjax::end(); ?>
            </div><!-- End of popup__body -->
        </div><!-- End of popup__content -->
    </div><!-- End of popup__container -->
</div>


<div class="popup popup-enter" id="popup_login">
    <div class="popup__container" style="max-width: 480px;">
        <div class="popup__content">
            <div class="popup__header">
                <h2><?= __('Kirish') ?></h2>
                <a href="javascript:void(0)" class="popup-close-btn"><i class="icon popup-close-icon"></i></a>
            </div><!-- End of popup__header -->

            <div class="popup__body">
                <?php Pjax::begin(['enablePushState' => false, 'timeout' => 10000, 'id' => 'pjax_login']) ?>
                <?php $form = ActiveForm::begin([
                                                    'id'              => 'form_login',
                                                    'action'          => Url::to(['account/login']),
                                                    'errorCssClass'   => 'has_error',
                                                    'successCssClass' => 'has_success',
                                                    'options'         => ['daata-pjax' => 1],
                                                ]); ?>

                <?= $form->field($loginModel, 'login', [
                    'template' => "<span class=\"before\"><i class=\"icon form-email-icon\"></i></span><span class=\"after\"><i class=\"icon form-error-icon\"></i><i class=\"icon form-success-icon\"></i></span>\n{input}",
                    'options'  => ['class' => 'form-row validate has_icon'],
                ])->textInput(['autofocus' => false, 'autocomplate' => false, 'placeholder' => __('Login')])->label(false) ?>

                <?= $form->field($loginModel, 'password', [
                    'template' => "<span class=\"before\"><i class=\"icon form-password-icon\"></i></span><span class=\"after\"><i class=\"icon form-error-icon\"></i><i class=\"icon form-success-icon\"></i></span>\n{input}",
                    'options'  => ['class' => 'form-row validate has_icon'],
                ])->passwordInput(['autofocus' => false, 'autocomplate' => false, 'placeholder' => __('Password')])->label(false) ?>

                <p class="form-group">
                    <?= Html::submitButton(__('Kirish'), ['class' => 'btn rounded']) ?>
                </p>

                <p class="enter-options"><span><?= __('Ijtimoiy tarmoqlar bilan kiring') ?></span></p>

                <?php $authChoice = AuthChoice::begin(['baseAuthUrl' => ['account/auth']]) ?>
                <p>
                    <?php foreach ($authChoice->getClients() as $i => $client) {
                        echo $authChoice->clientLink($client, $client->getTitle(), ['class' => 'btn rounded ' . $client->getName()]);
                        if ($i + 1 < count($authChoice->getClients())) {
                            echo "<span class='h-space'></span>";
                        }
                    } ?>
                </p>
                <?php AuthChoice::end() ?>

                <!--<div class="hr smaller"></div>
                <p class="mb-none"><a
                        href="<? /*= Url::to(['account/reset-password']) */ ?>"><? /*= __('Parolni unutdingizmi?') */ ?></a></p>
-->
                <div class="hr smaller"></div>
                <p><?= __('Akkauntingiz yo\'qmi?') ?><span class="h-space"></span><a href="#"
                                                                                     data-target="popup-registration"><?= __('Ro\'yhatdan o\'tish') ?></a>
                </p>
                <?php ActiveForm::end(); ?>
                <?php Pjax::end(); ?>
            </div><!-- End of popup__body -->
        </div><!-- End of popup__content -->
    </div><!-- End of popup__container -->
</div>


