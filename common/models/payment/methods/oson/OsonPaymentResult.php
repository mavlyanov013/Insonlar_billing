<?php
/*
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace common\models\payment\methods\oson;


class OsonPaymentResult extends \yii\base\Model
{
    public $status;
    public $transaction_id;
    public $bill_id;
    public $pay_url;
    public $error_code;
    public $message;

    public function isSuccess()
    {
        return !(0 < $this->error_code);
    }
}