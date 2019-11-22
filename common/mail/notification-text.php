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
/* @var $post common\models\Post */
/* @var $appeal common\models\Appeal */

$link = Yii::getAlias('@backendUrl/appeal/edit/') . $appeal->id;

?>

<?= __('Saytga murojaat kelib tushdi.') ?>


<?= __('Murojaatni ko\'rish uchun quyidagi havolani oching: ') ?>


<?= $link ?>
