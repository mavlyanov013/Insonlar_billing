<?php

namespace console\controllers;

use common\components\Config;
use common\models\payment\Payment;
use DateTime;
use Yii;
use yii\console\Controller;
use yii\mongodb\Query;

class TelegramController extends Controller
{
    public function actionDailySummary()
    {
        date_default_timezone_set('Asia/Tashkent');

        $now = time();
        $slotStart = floor($now / 10800) * 10800; // 3 soatlik slot
        $slotKey = date('Y-m-d H:i:s', $slotStart);

        $lastSentSlot = Config::get('telegram_last_daily_summary_slot');
        if ($lastSentSlot === $slotKey) {
            echo "Already sent for slot: {$slotKey}\n";
            return;
        }

        $today = new DateTime('today');
        $from = $today->getTimestamp() * 1000;
        $to = $now * 1000;

        $sum = (new Query())
    	->from(Payment::collectionName())
    	->where([
        	'time' => [
	            	'$gte' => $from,
        	    	'$lte' => $to,
        	],
        	'status' => 'success',
    	])
    	->sum('amount', Yii::$app->mongodb->getDatabase());
        	$sum = $sum ? $sum : 0;

        if ($sum > 0) {
            Yii::$app->telegram->sendDailySummary($sum, date('d-m-Y H:i:s', $now));
            Config::set('telegram_last_daily_summary_slot', $slotKey);
            echo "Summary sent: {$sum}\n";
        } else {
            echo "No payments today.\n";
        }
    }
	public function actionDebugPayments()
	{
    	$payments = \common\models\payment\Payment::find()
        	->orderBy(['_id' => SORT_DESC])
        	->limit(5)
        	->all();

    	foreach ($payments as $p) {
        	echo "AMOUNT: ".$p->amount.PHP_EOL;
        	echo "STATUS: ".$p->status.PHP_EOL;
        	echo "TIME: ".$p->time.PHP_EOL;
        	echo "------------------".PHP_EOL;
    }
}
}
