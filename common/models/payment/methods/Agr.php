<?php

namespace common\models\payment\methods;

use common\models\Order;
use common\models\payment\Method;
use common\models\payment\Payment;
use common\models\User;
use Yii;
use yii\base\Model;

class Agr extends Method
{

    const CONFIG_PAYMENT_URL = 'payment_url';
    const CONFIG_VENDOR_ID   = 'vendor_id';
    const CONFIG_SECRET_KEY  = 'secret_key';
    const CONFIG_RETURN_URL  = 'return_url';

    const CONFIG_MIN_AMOUNT  = 'minAmount';
    const CONFIG_MAX_AMOUNT  = 'maxAmount';

    const METHOD_CODE = 'agr';

    public $name        = 'PaSys';
    public $merchants   = [];
    public $description = 'Users can pay using PaSys payment aggregator';

    public function getPaymentUrl()
    {
        return $this->getConfig(self::CONFIG_PAYMENT_URL);
    }

    public function getVendorId()
    {
        return $this->getConfig(self::CONFIG_VENDOR_ID);
    }

    public function getReturnUrl()
    {
        return $this->getConfig(self::CONFIG_RETURN_URL);
    }

    public function getSecretKey()
    {
        return $this->getConfig(self::CONFIG_SECRET_KEY);
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