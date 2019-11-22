<?php

namespace common\models\payment\methods;

use common\models\payment\Method;
use common\models\payment\Payment;
use common\models\User;
use Yii;
use yii\base\Model;

/**
 * Created by PhpStorm.
 * User: complex
 * Date: 10/6/15
 * Time: 5:01 PM
 */
class Click extends Method
{
    const CONFIG_SECRET_KEY          = 'secret_key';
    const CONFIG_PAYMENT_URL         = 'payment_url';
    const CONFIG_RETURN_URL          = 'return_url';
    const CONFIG_INVOICE_URL         = 'invoice_url';
    const CONFIG_MERCHANT_ID         = 'merchant_id';
    const CONFIG_MERCHANT_USER_ID    = 'merchant_user_id';
    const CONFIG_MERCHANT_SERVICE_ID = 'service_id';

    const CONFIG_MIN_AMOUNT = 'minAmount';
    const CONFIG_MAX_AMOUNT = 'maxAmount';

    const METHOD_CODE = 'click';
    public $name        = 'Click';
    public $description = 'Users can pay using Click payment system';

    protected function isMethodAvailableForApp()
    {
        return true;
    }

    public function getMethodDescriptionForApp(User $user = null)
    {
        // TODO: Implement getMethodDescriptionForApp() method.
    }

    public function validateUserApp()
    {
        return true;
    }

    public function canBeUsedForAppPurchase()
    {
        return true;
    }

    public function canBeUsedForAppFundCustomerBalance()
    {
        return true;
    }

    protected function processPayment(Payment $payment)
    {
        //TODO
    }

    public function getSecretKey()
    {
        return $this->getConfig(self::CONFIG_SECRET_KEY);
    }

    public function getPaymentUrl()
    {
        return $this->getConfig(self::CONFIG_PAYMENT_URL);
    }

    public function getInvoiceUrl()
    {
        return $this->getConfig(self::CONFIG_INVOICE_URL);
    }

    public function getMerchantServiceId()
    {
        return $this->getConfig(self::CONFIG_MERCHANT_SERVICE_ID);
    }

    public function getMerchantUserId()
    {
        return $this->getConfig(self::CONFIG_MERCHANT_USER_ID);
    }

    public function getMerchantId()
    {
        return $this->getConfig(self::CONFIG_MERCHANT_ID);
    }

    public function getReturnUrl()
    {
        return $this->getConfig(self::CONFIG_RETURN_URL);
    }

    public function getAdditionalInformation(Model $payment)
    {
        // TODO: Implement getAdditionalInformation() method.
    }


    public function getMinAmount()
    {
        return intval($this->getConfig(self::CONFIG_MIN_AMOUNT));
    }

    public function getMaxAmount()
    {
        return intval($this->getConfig(self::CONFIG_MAX_AMOUNT));
    }


}