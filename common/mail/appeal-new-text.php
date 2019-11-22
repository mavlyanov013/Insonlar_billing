<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

/* @var $this yii\web\View */

use yii\helpers\Html;

/* @var $user common\models\User */
/* @var $appeal common\models\Appeal */

$link = Yii::getAlias('@frontendUrl/appeal/') . $appeal->number . '?t=' . $appeal->getNumberToken();
?>
<?= __('Assalomu alaykum, Sizdan {b}{child}{bc} uchun murojaat kelib tushdi.', ['child' => Html::encode($appeal->fullname)]) ?>


<?= __('Arizaning unikal raqami: {b}{number}{bc}', ['number' => $appeal->number]) ?>

<?php if (false): ?>
    <?= __('Murojaatingiz holatini quyidagi havola orqali ko\'rib borishingiz mumkin:') ?>

    <?= Html::a(Html::encode($link), $link) ?>
<?php endif; ?>