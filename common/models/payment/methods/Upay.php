<?php

namespace common\models\payment\methods;

use common\models\Order;
use common\models\payment\Method;
use common\models\payment\Payment;
use common\models\User;
use Yii;
use yii\base\Model;

class Upay extends Method
{

    const CONFIG_PAYMENT_URL = 'payment_url';
    const CONFIG_SERVICE_ID    = 'serviceId';
    const CONFIG_PASSWORD    = 'api_key';
    const CONFIG_MIN_AMOUNT  = 'minAmount';
    const CONFIG_MAX_AMOUNT  = 'maxAmount';
    const CONFIG_ALLOWED_IPS = 'allowedIps';

    const METHOD_CODE = 'upay';

    public $name        = 'Upay';
    public $merchants   = [];
    public $description = 'Users can pay using Upay payment system';

    public function getPaymentUrl()
    {
        return $this->getConfig(self::CONFIG_PAYMENT_URL);
    }

    public function getServiceId()
    {
        return $this->getConfig(self::CONFIG_SERVICE_ID);
    }

    public function getPassword()
    {
        return $this->getConfig(self::CONFIG_PASSWORD);
    }


    public function getAllowedIps()
    {
        return $this->getConfig(self::CONFIG_ALLOWED_IPS);
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