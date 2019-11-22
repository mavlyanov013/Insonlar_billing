<?php

namespace common\models\payment\methods\paymo\api;

use common\models\payment\methods\Paymo;
use common\models\payment\Payment;
use DateTime;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

/**
 * Created by PhpStorm.
 * User: complex
 * Date: 10/28/15
 * Time: 2:57 PM
 */
class PaymoMethod extends Model
{

    /**
     * @var Paymo
     */
    protected $_method;
    protected $_requestId;
    public    $account;
    public    $amount;
    public    $store_id;
    public    $transaction_id;
    public    $invoice;
    public    $sign;
    public    $transaction_time;


    public function rules()
    {
        return [
            [['transaction_time', 'amount'], 'required'],
            [['amount'], 'number', 'integerOnly' => true, 'min' => 0],
            [['sign'], 'validSignature'],
        ];
    }

    public function init()
    {
        $this->_method = Payment::getMethodInstance(Paymo::METHOD_CODE);
        parent::init();
    }

    public function validSignature($attribute)
    {
        $sign = md5(
            $this->store_id .
            $this->transaction_id .
            $this->account .
            $this->amount .
            $this->_method->getPassword()
        );

        if ($sign != $this->sign) {
            $this->addError($attribute, __('Invalid signature'));
        }
    }

    /***
     * @param $data
     * @return array
     * @throws PaymoError
     * @throws PaycomMerchantApiException
     */
    public static function processApiRequest($data)
    {
        $method = new self($data);

        if ($method->validate()) {
            if ($oldTransaction = $method->getTransaction($method->transaction_id)) {
                return [
                    'success' => 1,
                    'message' => __('Transaction with this id already registered on our billing'),
                ];
            } else {
                $transaction = new Payment();

                $transaction->create_time    = $method->getCurrentTimeStamp();
                $transaction->time           = $method->getTransactionDate();
                $transaction->status         = Payment::STATUS_SUCCESS;
                $transaction->method         = $method->_method->getCode();
                $transaction->transaction_id = $method->transaction_id;
                $transaction->amount         = $method->amount / 100;
                $transaction->user_data      = $method->account;

                $transaction->addAllInformation([
                    'invoice_id'       => $method->invoice,
                    'transaction_time' => $method->transaction_time,
                    'sign'             => $method->sign,
                ]);

                if ($transaction->save()) {
                    return [
                        'status' => 1,
                        'message' => __('Payment registered successfully'),
                    ];
                }

                throw new PaymoError(__('Unable save data'), 0);
            }
        }

        throw new PaymoError($method->getValidationError(), 0);
    }

    public function getTransactionDate()
    {
        if ($this->transaction_time) {
            try {
                $timestamp = DateTime::createFromFormat('Y-m-d H:i:s', $this->transaction_time);
                return $timestamp->getTimestamp() * 1000;
            } catch (\Exception $e) {

            }
        }

        return self::getCurrentTimeStamp();
    }

    protected function getValidationError()
    {
        $errors  = $this->getFirstErrors();
        $message = array_shift($errors);
        return $message ? $message : 'Error in request form';
    }


    /**
     * @param $transactionId string
     * @return bool | null|Payment | ActiveRecord
     */
    public function getTransaction($transactionId)
    {

        if ($transaction = Payment::find()
                                  ->where([
                                      'transaction_id' => $transactionId,
                                      'method'         => $this->_method->getCode(),
                                  ])
                                  ->one()) {
            return $transaction;
        }

        return false;
    }


    public static function getCurrentTimeStamp()
    {
        return round(microtime(true) * 1000);
    }
}
