<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

use frontend\models\LoginForm;
use frontend\models\SignupForm;
use yii\authclient\widgets\AuthChoice;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * Created by PhpStorm.
 * Date: 12/23/17
 * Time: 3:30 PM
 * @var $model SignupForm
 */
?>
<?php $form = ActiveForm::begin([
                                    'action'          => Url::to(['account/signup']),
                                    'errorCssClass'   => 'has_error',
                                    'successCssClass' => 'has_success',

                                ]); ?>

<?= $form->field($model, 'fullname', [
    'template' => "<span class=\"after\"><i class=\"icon form-error-icon\"></i><i class=\"icon form-success-icon\"></i></span>\n{input}",
    'options'  => ['class' => 'form-row validate'],
])->textInput(['autofocus' => false, 'placeholder' => __('Fullname')])->label(false) ?>

<?= $form->field($model, 'login', [
    'template' => "<span class=\"after\"><i class=\"icon form-error-icon\"></i><i class=\"icon form-success-icon\"></i></span>\n{input}",
    'options'  => ['class' => 'form-row validate'],
])->textInput(['autofocus' => false, 'placeholder' => __('Login')])->label(false) ?>

<?= $form->field($model, 'email', [
    'template' => "<span class=\"after\"><i class=\"icon form-error-icon\"></i><i class=\"icon form-success-icon\"></i></span>\n{input}",
    'options'  => ['class' => 'form-row validate'],
])->textInput(['autofocus' => false, 'placeholder' => __('Email')])->label(false) ?>

<?= $form->field($model, 'password', [
    'template' => "<span class=\"after\"><i class=\"icon form-error-icon\"></i><i class=\"icon form-success-icon\"></i></span>\n{input}",
    'options'  => ['class' => 'form-row mb-none validate'],
])->passwordInput(['autofocus' => false, 'placeholder' => __('Password')])->label(false) ?>
    <p class="form-hint"><?= __('Sizning parolingiz eng kamida 6 ta harfdan iborat bo’lishi kerak') ?></p>

<?= $form->field($model, 'confirmation', [
    'template' => "<span class=\"after\"><i class=\"icon form-error-icon\"></i><i class=\"icon form-success-icon\"></i></span>\n{input}",
    'options'  => ['class' => 'form-row validate'],
])->passwordInput(['autofocus' => false, 'placeholder' => __('Confirmation')])->label(false) ?>

<?= $form->field($model, 'agree', [
    'template' => "{input}\n{label}",
    'options'  => ['class' => 'form-row checkbox'],
])->checkbox() ?>

    <p class="form-group">
        <?= Html::submitButton(__('Akkount ochish'), ['class' => 'btn rounded']) ?>
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

    <div class="hr smaller"></div>

    <p><?= __('Menda akkount bor') ?><span class="h-space"></span>
        <a href="<?= Url::to(['account/login']) ?>" data-target="popup-enter"><?= __('Kirish') ?></a>
    </p>
<?php ActiveForm::end(); ?>