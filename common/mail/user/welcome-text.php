<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\Customer */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['account/confirm', 'token' => $user->password_reset_token]);
?>
<?= __('Hello {user}', ['user' => $user->getUsername()]) ?>,


<?= __('Welcome to {name} - our hi-tech online shop. Stay with us and be tuned to the latest news about technology.', ['name' => Yii::$app->name]) ?>


<?php if ($user->isConfirmationEnabled()): ?>
    <?= __('Follow the link below to activate your account:') ?>


    <?= Html::a(Html::encode($resetLink), $resetLink) ?>
<?php endif; ?>

