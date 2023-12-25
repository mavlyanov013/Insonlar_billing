<?php

namespace common\models\payment\methods\oson;

use common\models\payment\methods\Oson;
use common\models\payment\Payment;
use Yii;
use yii\base\Model;
use yii\web\UnprocessableEntityHttpException;

class ApiForm extends Model
{
    const METHOD_CHECK = 'check';
    const METHOD_PAY = 'pay';
    const METHOD_CHECK_STATUS = 'check_status';

    public $bill_id;
    public $transaction_id;
    public $error;
    public $error_note;
    public $sign_time;
    public $signature;
    public $status;
    public $amount;

    public $method;
    public $merchant_id;
    public $account;
    public $agent_transaction_id;
    public $params;

    /**
     * @var Oson
     */
    private $_method;

    public function init()
    {
        $this->_method = Payment::getMethodInstance(Oson::METHOD_CODE);
        parent::init();
    }

    public function rules()
    {
        return [
            [
                [
                    'method',
                    'merchant_id',
                    'account',
                    'agent_transaction_id',
                    'amount',
                    //'params',
                ],
                'required',
                'on' => [self::METHOD_CHECK, self::METHOD_PAY],
            ],
            [
                ['amount'],
                'number',
                'tooSmall' => __(
                    'Minimal to‘lov {min}',
                    ['min' => Yii::$app->formatter->asCurrency($this->_method->getMinAmount())]
                ),
                'tooBig' => __(
                    'Maksimal to‘lov {max}',
                    ['max' => Yii::$app->formatter->asCurrency($this->_method->getMaxAmount())]
                ),
                'min' => $this->_method->getMinAmount(),
                'max' => $this->_method->getMaxAmount(),
                //'integerOnly' => true,
                'skipOnEmpty' => false,
                'on' => [self::METHOD_CHECK, self::METHOD_PAY],
            ],
            //[['merchant_id'], 'validateMerchant', 'on' => [self::METHOD_CHECK, self::METHOD_PAY]],
            [['method', 'transaction_id'], 'required', 'on' => [self::METHOD_CHECK_STATUS]],
            [['signature'], 'validateSignature', 'on' => 'notify'],
        ];
    }

    public function validateSignature($attribute, $options)
    {
        $secret_key = $this->_method->getSecretToken();
        $merchant_id = $this->_method->getMerchantId();

        $sign = hash('sha256', $secret_key . ':' . $merchant_id);
        $sign = hash('sha256', $sign . ':' . sprintf('%s:%s:%s', $this->transaction_id, $this->bill_id, $this->status));
        if (hash_equals($sign, $this->signature) && $this->_method->getConfig('check_signature')) {
            throw new UnprocessableEntityHttpException('Signature check failed!');
        }
        return true;
    }

    public function validateMerchant($attribute, $options)
    {
        $merchant_id = $this->_method->getMerchantId();

        if ($merchant_id !== $this->$attribute) {
            throw new UnprocessableEntityHttpException('Account not found!', 3);
        }
        return true;
    }


    public static function processApiRequest($method, $postData)
    {
        switch ($method) {
            case self::METHOD_CHECK:
                $model = new ApiForm(['scenario' => self::METHOD_CHECK]);
                $model->load(['form' => $postData], 'form');
                return $model->check();
            case self::METHOD_PAY;
                $model = new ApiForm(['scenario' => self::METHOD_PAY]);
                $model->load(['form' => $postData], 'form');
                return $model->pay();
            case self::METHOD_CHECK_STATUS;
                $model = new ApiForm(['scenario' => self::METHOD_CHECK_STATUS]);
                $model->load(['form' => $postData], 'form');
                return $model->checkStatus();
        }

        throw new UnprocessableEntityHttpException('Method not found', -1);
    }

    public function check()
    {
        if ($payment = $this->getTransaction($this->agent_transaction_id)) {
            throw new UnprocessableEntityHttpException('Transaction already exists', -1);
        }
        if ($this->validate()) {
            return [
                'state' => 1,
                'success' => true,
            ];
        }

        throw new UnprocessableEntityHttpException($this->getValidationError(), -1);
    }

    /**
     * @return array
     * @throws UnprocessableEntityHttpException()
     */
    public function pay()
    {
        if ($this->validate()) {
            $userData = $this->getStoreUser();

            if (($payment = $this->getTransaction($this->agent_transaction_id)) && $payment) {
                throw new UnprocessableEntityHttpException('Transaction already exists', -1);
            } else {
                $payment = new Payment();

                $payment->status = Payment::STATUS_PENDING;
                $payment->time = $this->getTransactionTime();
                $payment->user_data = $userData;
                $payment->method = $this->_method->getCode();
                $payment->create_time = $this->getCurrentTimeStamp();
                $payment->transaction_id = $this->agent_transaction_id;
                $payment->amount = $this->amount;
                $payment->addAllInformation($this->params ?: []);

                if ($payment->save()) {
                    return [
                        'state' => 2,
                        'transaction_id' => $payment->transaction_id,
                    ];
                }

                throw new UnprocessableEntityHttpException('Failed to update user', -7);
            }
        }

        throw new UnprocessableEntityHttpException($this->getValidationError(), -8);
    }

    /**
     * @throws UnprocessableEntityHttpException
     */
    public function checkStatus()
    {
        $state = 2;
        if ($this->validate() && ($payment = $this->getTransaction($this->transaction_id))) {
            /*if ($payment->amount < 1000) {
                $this->addError('amount', __(
                    'Minimal to‘lov {min}',
                    ['min' => Yii::$app->formatter->asCurrency($this->_method->getMinAmount())]
                ));
                $state = -1;
            }*/

            if ($payment->status === Payment::STATUS_PENDING) {
                $payment->status = Payment::STATUS_SUCCESS;

                if ($payment->save()) {
                    return [
                        'state' => 1,
                    ];
                }

                Yii::error($payment->getErrorSummary(false), 'oson');
                $state = 2;
            } elseif ($payment->status == Payment::STATUS_SUCCESS || $payment->status == Payment::STATUS_FUNDED) {
                $state = -1;
            }
        }

        Yii::error($this->getValidationError(), 'oson');
        return [
            'state' => $state,
        ];
    }

    /**
     * @return string
     */
    protected function getStoreUser()
    {
        return iconv('windows-1251', 'utf-8', urldecode($this->account));
    }

    /**
     * @return string
     */
    protected function getTransactionTime()
    {
        return self::getCurrentTimeStamp();
    }

    /**
     * @param $transId int Oson Bill ID
     * @return Payment
     */
    public function getPaymentByBillingId($billId)
    {
        return Payment::findOne(
            [
                'method' => Oson::METHOD_CODE,
                'transaction_id' => $billId,
            ]
        );
    }

    /**
     * @param $transId string Transaction ID
     * @return Payment|bool
     */
    public function getTransaction($transId)
    {
        if ($transaction = Payment::find()
                                  ->where(
                                      [
                                          'transaction_id' => (int)$transId,
                                          'method' => Oson::METHOD_CODE,
                                      ]
                                  )
                                  ->one()) {
            $transaction->scenario = Payment::SCENARIO_UPDATE;
            return $transaction;
        }

        return false;
    }

    protected function getValidationError()
    {
        $errors = $this->getFirstErrors();
        $message = array_shift($errors);
        return $message ?: 'Error in request from oson';
    }

    public static function getCurrentTimeStamp()
    {
        return round(microtime(true) * 1000);
    }

}
