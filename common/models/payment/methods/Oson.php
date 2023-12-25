<?php

namespace common\models\payment\methods;

use common\models\payment\Method;
use yii\base\Model;

class Oson extends Method
{

    const CONFIG_PAYMENT_URL = 'payment_url';
    const CONFIG_MERCHANT_ID = 'merchant_id';
    const CONFIG_RETURN_URL  = 'return_url';
    const CONFIG_SECRET_TOKEN    = 'merchant_key';
    const CONFIG_MIN_AMOUNT  = 'minAmount';
    const CONFIG_MAX_AMOUNT  = 'maxAmount';

    const METHOD_CODE = 'oson';

    public $name        = 'Oson.uz';
    public $description = 'Users can pay using Oson.uz payment system';

    public function getPaymentUrl()
    {
        return $this->getConfig(self::CONFIG_PAYMENT_URL);
    }

    public function getMerchantId()
    {
        return $this->getConfig(self::CONFIG_MERCHANT_ID);
    }

    public function getReturnUrl()
    {
        return $this->getConfig(self::CONFIG_RETURN_URL);
    }

    public function getSecretToken()
    {
        return $this->getConfig(self::CONFIG_SECRET_TOKEN);
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