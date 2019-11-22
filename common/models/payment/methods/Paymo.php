<?php

namespace common\models\payment\methods;

use common\models\Order;
use common\models\payment\Method;
use common\models\payment\Payment;
use common\models\User;
use Yii;
use yii\base\Model;

class Paymo extends Method
{

    const CONFIG_PAYMENT_URL = 'payment_url';
    const CONFIG_STORE_ID    = 'store_id';
    const CONFIG_PASSWORD    = 'api_key';
    const CONFIG_MIN_AMOUNT  = 'minAmount';
    const CONFIG_MAX_AMOUNT  = 'maxAmount';

    const METHOD_CODE = 'paymo';

    public $name        = 'Paymo';
    public $merchants   = [];
    public $description = 'Users can pay using PayMo payment system';

    public function getPaymentUrl()
    {
        return $this->getConfig(self::CONFIG_PAYMENT_URL);
    }

    public function getStoreId()
    {
        return $this->getConfig(self::CONFIG_STORE_ID);
    }

    public function getPassword()
    {
        return $this->getConfig(self::CONFIG_PASSWORD);
    }

    public function getMinAmount()
    {
        return intval($this->getConfig(self::CONFIG_MIN_AMOUNT));
    }

    public function getMaxAmount()
    {
        return intval($this->getConfig(self::CONFIG_MAX_AMOUNT));
    }

    public function getAdditionalInformation(Model $payment)
    {
        return $payment->getAttributes(['create_time', 'perform_time', 'cancel_time']);
    }

}