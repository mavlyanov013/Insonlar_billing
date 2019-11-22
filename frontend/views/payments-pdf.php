<?php
/**
 * @var $payment Payment
 */

use common\models\payment\Payment;

$payments = Payment::getTodayPayments();
$sum      = 0;
?>
<table class="table table-striped">
    <thead>
    <tr>
        <td width="45px"><b>№</b></td>
        <td width="15%"><b>Summa</b></td>
        <td width="15%"><b>Turi</b></td>
        <td width="20%"><b>Vaqt</b></td>
        <td ><b>Izoh</b></td>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($payments as $i => $payment): $sum += $payment->amount ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= Yii::$app->formatter->asInteger($payment->amount) ?></td>
            <td><?= $payment->getMethodLabel() ?></td>
            <td><?= $payment->getPaymentDateFormattedAsTime() ?></td>
            <td><?= $payment->user_data ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<p>
    <?= $date ?> ga qadar kunlik jami tushum: <b><?= Yii::$app->formatter->asCurrency($sum) ?></b>
</p>
<p>Saxovat Qo'qon guruhiga a'zo bo'ling: <a href="https://t.me/saxovatguruhi">@saxovatguruhi</a></p>
