<?php

namespace common\models\payment\methods;

use common\models\Order;
use common\models\payment\Method;
use common\models\payment\Payment;
use common\models\User;
use Yii;
use yii\base\Model;

class Cash extends Method
{

    const CONFIG_MIN_AMOUNT = 'minAmount';
    const CONFIG_MAX_AMOUNT = 'maxAmount';

    const METHOD_CODE = 'cash';

    public $name        = 'Cash';
    public $merchants   = [];
    public $description = 'Users can pay using cash banknotes';

    public function processOrder(Order $order)
    {
        $order->status = Order::STATUS_PENDING;
    }


    public function getMethodDescriptionForApp(User $user = null)
    {
        // TODO: Implement getMethodDescriptionForApp() method.
    }

    protected function processPayment(Payment $payment)
    {
        // TODO: Implement processPayment() method.
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