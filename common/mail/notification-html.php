<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

/* @var $this yii\web\View */

use common\models\Contest;
use yii\helpers\Html;

/* @var $user common\models\User */
/* @var $appeal common\models\Appeal */

$link = Yii::getAlias('@backendUrl/appeal/edit/') . $appeal->id;
?>

<p>
    <?= __('Saytga murojaat kelib tushdi.') ?>
</p>

<p>
    <?= __('Murojaatni ko\'rish uchun quyidagi havolani oching: ') ?>
</p>
<p><?= Html::a(Html::encode($link), $link) ?></p>
