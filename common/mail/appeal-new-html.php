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
    <p>
        <?= __('Assalomu alaykum, Sizdan {b}{child}{bc} uchun murojaat kelib tushdi.', ['child' => Html::encode($appeal->fullname)]) ?>
    </p>
    <p>
        <?= __('Arizaning unikal raqami: {b}{number}{bc}', ['number' => $appeal->number]) ?>
    </p>
<?php if (false): ?>
    <p>
        <?= __('Murojaatingiz holatini quyidagi havola orqali ko\'rib borishingiz mumkin:') ?>
    </p>
    <p><?= Html::a(Html::encode($link), $link) ?></p>
<?php endif; ?>