<?php

namespace common\models\payment\methods\apelsin;

use common\models\payment\methods\Apelsin;
use common\models\payment\methods\Oson;
use common\models\payment\Payment;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\web\UnprocessableEntityHttpException;

class ApiForm extends Model
{
    const METHOD_CHECK = 'check';
    const METHOD_PAY   = 'create';

    public $method;
    public $price;
    public $amount;
    public $operationId;
    public $orderId;
    public $account;
    public $params;

    /**
     * @var Oson
     */
    private $_method;

    public function init()
    {
        $this->_method = Payment::getMethodInstance(Apelsin::METHOD_CODE);
        parent::init();
    }

    public function rules()
    {
        return [
            [
                [
                    'operationId',
                    'orderId',
                    'price',
                    'amount',
                ],
                'safe',
                'on' => [
                    self::METHOD_CHECK,
                    self::METHOD_PAY,
                ]
            ],
        ];
    }

    public static function processApiRequest($method, $postData)
    {
        switch ($method) {
            case self::METHOD_CHECK:
                $model = new self(['scenario' => self::METHOD_CHECK]);
                $model->load(['form' => $postData], 'form');
                return $model->check();
            case self::METHOD_PAY;
                $model = new self(['scenario' => self::METHOD_PAY]);
                $model->load(['form' => $postData], 'form');
                return $model->pay();
        }

        throw new ApelsinError('Method not found', -1);
    }

    public function check()
    {
        if ($this->validate()) {
            return [
                'message' => 'OK',
                'success' => true,
                'price'   => round(floatval($this->price ?: $this->amount), 2),
                'orderId' => $this->orderId,
            ];
        }

        throw new ApelsinError($this->getValidationError(), -1);
    }

    /**
     * @return array
     * @throws UnprocessableEntityHttpException()
     */
    public function pay()
    {
        if ($this->validate()) {
            $userData = $this->orderId;

            if (($payment = $this->getTransaction($this->operationId)) && $payment) {
                throw new ApelsinError('Transaction already exists', -1);
            } else {
                $payment = new Payment();

                $payment->status         = Payment::STATUS_SUCCESS;
                $payment->time           = $this->getTransactionTime();
                $payment->user_data      = $userData;
                $payment->method         = $this->_method->getCode();
                $payment->create_time    = $this->getCurrentTimeStamp();
                $payment->transaction_id = $this->operationId;
                $payment->amount         = round($this->amount / 100, 2);

                if ($payment->save()) {
                    return [
                        'success' => true,
                        'message' => 'OK',
                    ];
                }

                throw new ApelsinError('Failed to update user', -7);
            }
        }

        throw new ApelsinError($this->getValidationError(), -8);
    }

    /**
     * @return string
     */
    protected function getTransactionTime()
    {
        return self::getCurrentTimeStamp();
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
                    'transaction_id' => $transId,
                    'method'         => Apelsin::METHOD_CODE,
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
        $errors  = $this->getFirstErrors();
        $message = array_shift($errors);
        return $message ?: 'Error in request from Apelsin';
    }

    public static function getCurrentTimeStamp()
    {
        return round(microtime(true) * 1000);
    }

}
