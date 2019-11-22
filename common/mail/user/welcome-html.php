<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\Customer */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['account/confirm', 'token' => $user->password_reset_token]);
?>
<div>
    <p><?= __('Hello {user}', ['user' => $user->getUsername()]) ?>,</p>

    <p><?= __('Welcome to {name} - our hi-tech online shop. Stay with us and be tuned to the latest news about technology.', ['name' => Yii::$app->name]) ?></p>

    <?php if ($user->isConfirmationEnabled()): ?>
        <p><?= __('Follow the link below to activate your account:') ?></p>

        <p><?= Html::a(Html::encode($resetLink), $resetLink) ?></p>
    <?php endif; ?>

</div>
